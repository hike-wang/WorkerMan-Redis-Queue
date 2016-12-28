<?php
/**
 * Created by PhpStorm.
 * User: hike
 * Date: 2016/12/28
 * Time: 22:32
 */


use Workerman\Worker;
use Clue\React\Redis\Factory;
use Clue\React\Redis\Client;
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/vendor/autoload.php';

// 创建一个Worker监听2345端口，使用websocket协议通讯
$ws_worker = new Worker("websocket://0.0.0.0:2345");
$ws_worker::$stdoutFile = __DIR__ .'/logs/read.log';
// 启动4个进程对外提供服务
$ws_worker->count = 4;


$ws_worker->onWorkerStart = function() {
    global $factory;
    $loop    = Worker::getEventLoop();
    $factory = new Factory($loop);
};

$ws_worker->onMessage = function($connection, $data)
{
    //接受入队列通知
    global $factory;
    //出redis队列
    $factory->createClient('localhost:6379')->then(function (Client $client)  {
        $client->rPop('test')->then(function ($result) {
            echo $result . PHP_EOL;
        });
    });
};

// 运行worker
Worker::runAll();