<?php 
use \Workerman\Worker;
use \Workerman\WebServer;
use \Workerman\Protocols\Websocket;

// 自动加载类
require_once __DIR__ . '/../../Workerman/Autoloader.php';

$recv_worker = new Worker('Websocket://0.0.0.0:8080');
$recv_worker->onWorkerStart = function($recv_worker)
{
    $send_worker = new Worker('Websocket://0.0.0.0:8008');
    $send_worker->onMessage = function($connection, $data)
    {
    };
    $recv_worker->sendWorker = $send_worker;
    $send_worker->listen();
};

$recv_worker->onMessage = function($connection, $data)use($recv_worker)
{
    foreach($recv_worker->sendWorker->connections as $send_connection)
    {
        //$send_connection->websocketType = "\x82";
        $send_connection->send($data);
    }
};

// WebServer
$web = new WebServer("http://0.0.0.0:8088");
// WebServer数量
$web->count = 2;
// 设置站点根目录
$web->addRoot('www.your_domain.com', __DIR__.'/Web');


// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
