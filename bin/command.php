<?php
require_once __DIR__ . '/../lib/FSC.php';
require_once __DIR__ . '/../controller/Controller.php';

$config = require_once __DIR__ . '/../conf/app.php';
$default_timezone = !empty($config['default_timezone']) ? $config['default_timezone'] : 'Asia/HongKong';
date_default_timezone_set($default_timezone);

//set variables
$action = isset($argv[1]) ? $argv[1] : 'index';
$_SERVER['REQUEST_URI'] = "/command/{$action}";

//GET parameters, format: foo=bar&hello=world
if (!empty($argv[2])) {
    $_SERVER['QUERY_STRING'] = $argv[2];
    $arr = explode('&', $argv[2]);
    if (!isset($_GET)) {$_GET = array();}
    foreach ($arr as $item) {
        $ar = explode('=', $item);
        $_GET[$ar[0]] = $ar[1];
    }
}

//run app
FSC::run($config);
