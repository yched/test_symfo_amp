<?php

include (__DIR__ . '/../vendor/autoload.php');

use Amp\Promise;
use function Amp\ParallelFunctions\parallelMap;

$values = Promise\wait(parallelMap([1, 2, 3], function ($time) {
    \sleep($time); // a blocking function call, might also do blocking I/O here

    return $time * $time;
}));

var_export($values);

