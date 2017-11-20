<?php

namespace AppBundle\Controller;

use Amp\Artax\Response;
use Amp\Promise;
use Amp\Success;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function exampleAsyncAction(Request $request)
    {
        $results = yield [
            $this->somethingAsync('a', 1),
            $this->somethingAsync('b', 1),
            $this->somethingAsync('c', 1),
        ];
        $sum = array_reduce(
            $results,
            function ($acc, $result) {return $acc + $result;}
        );

        $result = yield $this->fetchFromWebAsync($sum * 2);

        return $this->render('default/index.html.twig', ['content' => $result]);
    }

    /**
     * @Route("/bench_async/{count}", name="bench_async")
     */
    public function benchAsyncAction($count = 1, Request $request)
    {
        $result = 0;
        for ($i = 0; $i < $count; $i++) {
            $result += yield new Success(1);
        }

        return $this->render('default/index.html.twig', ['content' => $result]);
    }

    /**
     * @Route("/bench/{count}", name="bench")
     */
    public function benchAction($count = 1, Request $request)
    {
        $result = 0;
        for ($i = 0; $i < $count; $i++) {
            $result += 1;
        }

        return $this->render('default/index.html.twig', ['content' => $result]);
    }

    /**
     * @Route("/db_async/{count}", name="db_async")
     */
    public function dbAsyncAction($count = 1, Request $request)
    {
        $result = 0;
        for ($i = 0; $i < $count; $i++) {
            $result += yield $this->fetchFromDbAsync('a', 0.003);
        }

        return $this->render('default/index.html.twig', ['content' => $result]);
    }

    /**
     * @Route("/db/{count}", name="db")
     */
    public function dbAction($count = 1, Request $request)
    {
        $result = 0;
        for ($i = 0; $i < $count; $i++) {
            $result += $this->fetchFromDb('a', 0.003);
        }

        return $this->render('default/index.html.twig', ['content' => $result]);
    }




    protected function fetchFromDb($name, $delay = 0)
    {
        $db = $this->get('database_connection');
        $stopwatch = $this->get('debug.stopwatch');

        $stopwatchName = $this->uniqueStopwatchName("select $name");
        $stopwatch->start($stopwatchName);

        $row = $db->fetchAssoc(
            "SELECT value, :rand, SLEEP(:delay) FROM tmp WHERE name = :name",
            ['rand' => rand(), 'delay' => $delay, 'name' => $name]
        );

        $stopwatch->stop($stopwatchName);

        return $row['value'];
    }

    protected function fetchFromDbAsync($name, $delay = 0): Promise
    {
        return \Amp\call(function () use ($name, $delay) {
            $db = $this->get('app.db');
            $stopwatch = $this->get('debug.stopwatch');

            $stopwatchName = $this->uniqueStopwatchName("select $name");
            $stopwatch->start($stopwatchName);

            /** @var $results \Amp\Mysql\ResultSet */
            $results = yield $db->query(sprintf(
                "SELECT value, %f, SLEEP(%f) FROM tmp WHERE name = '%s'",
                rand(), $delay, $name
            ));
            $row = yield $results->fetchAssoc();

            $stopwatch->stop($stopwatchName);

            return $row['value'];
        });
    }

    protected function fetchFromWebAsync($param, $delay = 0): Promise
    {
        return \Amp\call(function () use ($delay, $param) {
            $client = $this->get('app.artax');
            $stopwatch = $this->get('debug.stopwatch');

            $url = "http://httpbin.org/delay/$delay?result=$param";
            $stopwatchName = $this->uniqueStopwatchName($url);
            $stopwatch->start($stopwatchName);

            /** @var $response Response */
            $response = yield $client->request($url);
            $body = yield $response->getBody()->read();
            $data = json_decode($body, true);

            $stopwatch->stop($stopwatchName);

            return $data['args']['result'];
        });
    }

    protected function somethingAsync($name, $delay = 0): Promise
    {
        return \Amp\call(function () use ($name, $delay) {
            $param = yield $this->fetchFromDbAsync($name, $delay);
            return $this->fetchFromWebAsync($param, $delay);
        });
    }

    protected function uniqueStopwatchName($name)
    {
        static $counters = [];

        $counters += [$name => 0];
        $uniqueName = $name . ($counters[$name] ? " ($counters[$name])" : '');
        $counters[$name]++;

        return $uniqueName;
    }
}
