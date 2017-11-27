<?php

require ('./common.php');

function my_generator($count, $seed = 0) {
    for ($i = 0; $i <= $count; $i++) {
        $seed = yield rand($seed * 100, ($seed + 1) * 100);
        println("generator received $seed");
    }
}

$it = my_generator(5);
while ($it->valid()) {
    println("generator yielded " . $it->current());
    $it->send(rand(1, 5));
}
