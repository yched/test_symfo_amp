<?php

function range_generator($start, $end, $step = 1) {
    for ($i = $start; $i <= $end; $i += $step) {
        yield $i;
    }
}

$range = range_generator(5, 10, 2); // instanceof \Generator
foreach ($range as $key => $value) {
    echo "$key => $value" . PHP_EOL;
}
