<?php

include (__DIR__ . '/../vendor/autoload.php');

use function Amp\ParallelFunctions\parallel;

$promise = \Amp\coroutine(function () {
    $delay = 2;
    $sync = parallel(function () use ($delay) {
        sleep($delay);
        return $delay * $delay;
    });
    $promise = \Amp\call(function () use ($sync) {
        $value = yield $sync();
        return $value;
    });
    $value = yield $promise;
    return $value;
})();
$result = \Amp\Promise\wait($promise);


