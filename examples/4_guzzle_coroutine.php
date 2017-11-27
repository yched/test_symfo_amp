<?php

// composer require guzzlehttp/guzzle
include ('../vendor/autoload.php');

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;

$promises = [
    'task1' => taskAsync('task 1'),
    'task2' => taskAsync('task 2'),
];
$results = Promise\settle($promises)->wait();

println('-----------');
array_walk($results, function ($result, $key) {
    println("result $key : $result[value]");
});

/**
 * @return \GuzzleHttp\Promise\PromiseInterface
 */
function taskAsync($name) {
    return Promise\coroutine(function () use ($name) {
        $sum = 0;

        println("$name : start 1");
        $result = yield getResultAsync($name, 1, rand(0, 4));
        println("$name : end 1 (result $result)");
        $sum += $result;

        println("$name : start 2");
        $result = yield getResultAsync($name, 2, rand(0, 4));
        println("$name : end 2 (result $result)");
        $sum += $result;

        yield $sum;
    });
}

/**
 * @return \GuzzleHttp\Promise\PromiseInterface
 */
function getResultAsync($name, $value, $delay) {
    return Promise\coroutine(function () use ($name, $value, $delay) {
        $req = new Request('GET', "http://httpbin.org/delay/$delay?name=$name&value=$value");
        /* @var $res \GuzzleHttp\Psr7\Response */
        $res = yield client()->sendAsync($req);
        $data = json_decode($res->getBody()->getContents(), true);
        yield $data['args']['value'];
    });
}

/**
 * @return \GuzzleHttp\Client
 */
function client() {
    static $client;
    $client = $client ?? new Client();
    return $client;
}

function println($string) {
    echo "$string\n";
}
