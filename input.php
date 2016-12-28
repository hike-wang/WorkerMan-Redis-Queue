<?php
/**
 * Created by PhpStorm.
 * User: hike
 * Date: 2016/12/28
 * Time: 22:32
 */

// 建立socket连接到内部推送端口
$client = stream_socket_client('tcp://127.0.0.1:6666', $errno, $errmsg, 1);
$i = 1;
while ($i <= 100){
// 推送的数据
    $arr = array('dateline' => time().rand(1000,9999));

// 发送数据，注意6666端口是Text协议的端口，Text协议需要在数据末尾加上换行符
    fwrite($client, json_encode($arr)."\n");
// 读取推送结果
    echo fread($client, 8192) . PHP_EOL;
    echo $i . PHP_EOL;
    $i++;
}