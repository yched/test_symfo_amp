<?php

function range_generator($start, $end, $step) {
    for ($i = $start; $i <= $end; $i += $step) {
        yield $i;
    }
}

$range = range_generator(5, 10, 2);
foreach ($range as $key => $value) {
    echo "$key => $value" . PHP_EOL;
}
