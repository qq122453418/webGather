<?php
require_once "./vendor/autoload.php";
$config = include('./config.php');
$gather = new Gather($config);
// $gather->setSaveDir(__DIR__ . '/db');
$gather->setOnResult(
    function($result, $num, $remote_URI)
    {
        echo $result['title'].PHP_EOL;
    }
);
$gather->exec();
