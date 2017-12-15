<?php

// composer require guzzlehttp/guzzle
include ('../vendor/autoload.php');
include ('./common.php');

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise;



$promise = client()->sendAsync(new Request('GET', "http://httpbin.org/delay/1?value=1"));
$response = $promise->wait();
println($response->getStatusCode());
println('-----------');



$promises = [
    'one' => client()->sendAsync(new Request('GET', "http://httpbin.org/delay/1?value=1")),
    'two' => client()->sendAsync(new Request('GET', "http://httpbin.org/delay/1?value=2")),
];
$responses = Promise\all($promises)->wait();
var_export(array_map(function ($response) {return $response->getStatusCode();}, $responses));
