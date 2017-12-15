<?php

require ('./common.php');

function my_generator($count, $seed = 0) {
    for ($i = 0; $i <= $count; $i++) {
        $seed = yield rand($seed * 100, ($seed + 1) * 100);
        println("generator received $seed");
    }
}

$it = my_generator(10);
$count = 0;
$it->send(rand(1, 5));
while ($it->valid()) {
    println("$count : generator yielded " . $it->current());
    if ($count !== 3) {
        $it->send(rand(1, 5));
    }
    else {
        $it->throw(new \RuntimeException('Bad luck!'));
    }
    $count++;
}
