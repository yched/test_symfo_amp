<?php

use GuzzleHttp\Client;

/**
 * @return \GuzzleHttp\Client
 */
function client() {
    static $client;
    $client = $client ?? new Client();
    return $client;
}

function println($string) {
    echo $string . PHP_EOL;
}
