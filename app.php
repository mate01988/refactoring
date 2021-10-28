<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";

global $argv;

try {
    $system = new \App\System($argv);
    echo implode(PHP_EOL, $system->run()) . PHP_EOL;

} catch (\App\Exception\AppException $exception) {
    echo $exception->getMessage() . PHP_EOL;
    exit(1);

} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
    exit(1);
}