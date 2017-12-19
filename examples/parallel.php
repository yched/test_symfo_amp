<?php

include (__DIR__ . '/../vendor/autoload.php');

use Amp\Promise;
use function Amp\ParallelFunctions\parallel;

$delay = 2;
$sync = parallel(function () use ($delay) {
    sleep($delay);
    return $delay * $delay;
});
$values = Promise\wait($sync());

var_export($values);

