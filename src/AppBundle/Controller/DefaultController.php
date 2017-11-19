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
            $this->fetchAsync('a', 1),
            $this->fetchAsync('b', 1),
            $this->fetchAsync('c', 1),
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
            $result += yield $this->fetchFromDbAsync('a');
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
            $result += $this->fetchFromDb('a');
        }

        return $this->render('default/index.html.twig', ['content' => $result]);
    }

    protected function fetchAsync($name, $delay = 0): Promise
    {
        return \Amp\call(function () use ($name, $delay) {
            $param = yield $this->fetchFromDbAsync($name, $delay);
            return $this->fetchFromWebAsync($param, $delay);
        });
    }

    protected function fetchFromDbAsync($name, $delay = 0): Promise
    {
        return \Amp\call(function () use ($name, $delay) {
            $db = $this->get('app.db');

            /** @var $results \Amp\Mysql\ResultSet */
            $query = yield $db->prepare(
                "SELECT value, :rand, SLEEP(:delay) FROM tmp WHERE name = :name",
                ['delay' => $delay, 'name' => $name, 'rand' => rand()]
            );
            $row = yield $query->fetchAssoc();

            return $row['value'];
        });
    }

    protected function fetchFromDb($name, $delay = 0)
    {
        $db = $this->get('database_connection');
        $row = $db->fetchAssoc(
            "SELECT value, :rand, SLEEP(:delay) FROM tmp WHERE name = :name",
            ['delay' => $delay, 'name' => $name, 'rand' => rand()]
        );
        return $row['value'];
    }

    protected function fetchFromWebAsync($param, $delay = 0): Promise {
        return \Amp\call(function () use ($delay, $param) {
            $client = $this->get('app.artax');

            /** @var $response Response */
            $response = yield $client->request("http://httpbin.org/delay/$delay?result=$param");
            $body = yield $response->getBody()->read();
            $data = json_decode($body, true);

            return $data['args']['result'];
        });
    }
}
