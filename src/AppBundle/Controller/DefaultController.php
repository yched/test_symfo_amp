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
            $this->getResult(1, 1),
            $this->getResult(1, 2),
            $this->getResult(1, 3),
        ];
        $sum = array_reduce(
            $results,
            function ($acc, $result) {return $acc + $result;}
        );

        $result = yield $this->getResult(0, $sum * 2);

        return $this->render('default/index.html.twig', array(
            'content' => $result
        ));
    }

    protected function getResult($delay, $param): Promise {
        return \Amp\call(function () use ($delay, $param) {
            $client = $this->get('app.artax');
            /** @var $response Response */
            $response = yield $client->request(
                "http://httpbin.org/delay/$delay?result=$param"
            );
            $body = yield $response->getBody()->read();
            $data = json_decode($body, true);

            return $data['args']['result'];
        });
    }
}
