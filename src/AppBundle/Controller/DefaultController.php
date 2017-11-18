<?php

namespace AppBundle\Controller;

use Amp\Artax\Response;
use function Amp\coroutine;
use function Amp\Promise\all;
use Amp\Loop;
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
        $return = null;
        \Amp\asyncCall(function () use (&$return) {
            $logger = $this->get('logger');
            $logger->info('runs');
            $results = yield all([
                $this->getResult(1, 1),
                $this->getResult(1, 2),
                $this->getResult(1, 3),
            ]);
            $sum = array_reduce(
                $results,
                function ($acc, $result) {return $acc + $result;},
                0
            );
            $result = yield $this->getResult(0, $sum * 2);
            $return = $this->render('default/index.html.twig', array(
                'content' => $result
            ));
            Loop::stop();
        });
        Loop::run();
        return $return;
    }

    protected function getResult($delay, $param) {
        return \Amp\call(function ($delay, $param) {
            $client = $this->get('app.artax');
            /** @var $response Response */
            $response = yield $client->request(
                "https://httpbin.org/delay/$delay?result=$param"
            );
            $body = yield $response->getBody()->read();
            $data = json_decode($body, true);

            return $data['args']['result'];
        }, $delay, $param);
    }
}
