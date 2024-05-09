<?php
/* All php controller enter from here */
$start_time = microtime(true);        //for time cost
$config = require_once __DIR__ . '/../conf/app.php';
$default_timezone = !empty($config['default_timezone']) ? $config['default_timezone'] : 'Asia/Hong_Kong';
date_default_timezone_set($default_timezone);
$config['start_time'] = $start_time;

require_once __DIR__ . '/../lib/FSC.php';
require_once __DIR__ . '/../controller/Controller.php';

//run app
FSC::run($config);