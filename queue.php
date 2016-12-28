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
use Workerman\Connection\AsyncTcpConnection;
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/vendor/autoload.php';


$text_worker = new Worker("text://0.0.0.0:6666");
$text_worker->count = 4;
$text_worker::$stdoutFile = __DIR__.'/logs/queue.log';

$text_worker->onWorkerStart = function() {
    global $factory , $con;
    $loop    = Worker::getEventLoop();
    $factory = new Factory($loop);
    $con = new AsyncTcpConnection('ws://0.0.0.0:2345');
};

$text_worker->onMessage =  function($connection, $data)
{
    global $factory;

    $factory->createClient('localhost:6379')->then(function (Client $client) use ($connection ,$data) {
        $arr = json_decode($data , true);
        if(empty($arr)) return;

        global   $con;
        $con->onConnect = function($con) {
            echo '1 ';
        };

        //入redis 队列
        $client->lPush('test',$arr['dateline'])->then(function () use ($connection ,$con){
            echo 'ok'. PHP_EOL;

            // 通知read(消费者) 我往队列中 写了一条数据  你看着办
            $con->send(1);
            $con->connect();
            $connection->send('ok');
        });
    });
};

Worker::runAll();
