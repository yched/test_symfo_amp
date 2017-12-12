<?php

// composer require guzzlehttp/guzzle
include ('../vendor/autoload.php');
include ('./common.php');

use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$promises = [
    'task1' => taskAsync('task 1'),
    'task2' => taskAsync('task 2'),
];
$results = Promise\all($promises)->wait();

println('-----------');
var_export($results);

/**
 * @return \GuzzleHttp\Promise\PromiseInterface
 */
function taskAsync($name) {
    $sum = 0;

    println("$name : start 1");
    $promise = getResultAsync($name, 1, rand(0, 4))
        ->then(function ($result) use ($name, &$sum) {
            $sum += $result;
            println("$name : end 1 (result $result)");
        })
        ->then(function () use ($name) {
            println("$name : start 2");
            return getResultAsync($name, 2, rand(0, 4));
        })
        ->then(function ($result) use ($name, &$sum) {
            $sum += $result;
            println("$name : end 2 (result $result)");
            return $sum;
        });

    return $promise;
}

/**
 * @return \GuzzleHttp\Promise\PromiseInterface
 */
function getResultAsync($name, $value, $delay) {
    $req = new Request('GET', "http://httpbin.org/delay/$delay?name=$name&value=$value");
    $promise = client()->sendAsync($req)
        ->then(function (Response $res) {
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['args']['value'];
        });

    return $promise;
}
