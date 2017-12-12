<?php

// composer require guzzlehttp/guzzle
include ('../vendor/autoload.php');
include ('./common.php');

use GuzzleHttp\Psr7\Request;

$results = [
    'task1' => task('task 1'),
    'task2' => task('task 2'),
];

println('-----------');
var_export($results);

/**
 * @return int
 */
function task($name) {
    $sum = 0;

    println("$name : start 1");
    $result1 = getResult($name, 1, rand(0, 4));
    println("$name : end 1 (result $result1)");
    $sum += $result1;

    println("$name : start 2");
    $result2 = getResult($name, $result1 + 1, rand(0, 4));
    println("$name : end 2 (result $result2)");
    $sum += $result2;

    return $sum;
}

/**
 * @return int
 */
function getResult($name, $value, $delay) {
    // Exemple : curl "http://httpbin.org/delay/1?foo=bar&zoo=glu"
    $req = new Request('GET', "http://httpbin.org/delay/$delay?name=$name&value=$value");

    $res = client()->send($req);
    $data = json_decode($res->getBody()->getContents(), true);

    return $data['args']['value'];
}
