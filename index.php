<?php
require 'vendor/autoload.php';


date_default_timezone_set('Asia/Shanghai');

use Workerman\Worker;
use Tools\Route;

define('APP_ROOT',__DIR__);
define('APP_SITE','App');
define('APP_ENV','local');

$wk = new Worker('http://0.0.0.0:9194');
$wk->onMessage = function ($connect, $data){
    try{
        Route::parse_var($data);
        $cont = Route::parse($data['server']['REQUEST_URI']);
        $connect->send($cont);
    }catch (\Exception $e){
        $connect->send($e->getMessage());
    }
};
Worker::runAll();