<?php

namespace AppBundle\Controller;

use Amp\Artax\Response;
use Amp\Promise;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $results = yield [
            $this->fetch('a', 1),
            $this->fetch('b', 1),
            $this->fetch('c', 1),
        ];
        $sum = array_reduce(
            $results,
            function ($acc, $result) {return $acc + $result;}
        );

        $result = yield $this->fetchFromWeb($sum * 2);

        return $this->render('default/index.html.twig', array(
            'content' => $result,
        ));
    }

    protected function fetch($name, $delay = 0): Promise
    {
        return \Amp\call(function () use ($name, $delay) {
            $param = yield $this->fetchFromDb($name, $delay);
            return $this->fetchFromWeb($param, $delay);
        });
    }

    protected function fetchFromDb($name, $delay = 0): Promise
    {
        return \Amp\call(function () use ($name, $delay) {
            $db = $this->get('app.db');

            /** @var $results \Amp\Mysql\ResultSet */
            $query = yield $db->prepare(
                "SELECT value, SLEEP(:delay) FROM tmp WHERE name = :name",
                ['delay' => $delay, 'name' => $name]
            );
            $row = yield $query->fetchAssoc();

            return $row['value'];
        });
    }

    protected function fetchFromWeb($param, $delay = 0): Promise {
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
