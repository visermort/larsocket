<?php

namespace App\Servers;

use Workerman\Worker;
use Illuminate\Support\Facades\Config;

/**
 * Примеры отсюда https://github.com/morozovsk/workerman-examples/tree/master
 */
class WorkermanServer
{
    protected $users = [];


    public function run()
    {
        // Create a Websocket server
        $ws_worker = new Worker('websocket://' . Config::get('workerman.socket_url'));

//        // Emitted when new connection come
//        $ws_worker->onConnect = function ($connection) {
//            echo "New connection\n";
//            echo print_r($connection, true);
//        };
//
//        // Emitted when data received
//        $ws_worker->onMessage = function ($connection, $data) {
//            // Send hello $data
//            echo print_r($connection,true);
//            echo print_r($data,true);
//            //$connection->send('Hello ' . $data);
//        };
//
//        // Emitted when connection closed
//        $ws_worker->onClose = function ($connection) {
//            echo "Connection closed\n";
//        };


        $ws_worker->onWorkerStart = function()
        {
            echo "worker_start\n";
            // создаём локальный tcp-сервер, чтобы отправлять на него сообщения из кода нашего сайта
            $inner_tcp_worker = new Worker('tcp://' . Config::get('workerman.tcp_url'));
            // создаём обработчик сообщений, который будет срабатывать,
            // когда на локальный tcp-сокет приходит сообщение
            $inner_tcp_worker->onMessage = function($connection, $response) {
                echo "message\n";
                $data = json_decode($response);
                echo print_r($response, true);
                // для примера - отправляем сообщение пользователю по userId
                if (isset($this->users[$data->user])) {
                    $wsConnection = $this->users[$data->user];
                    $sendResult = $wsConnection->send(json_encode(['message' => $data->message]));
                    echo print_r($sendResult, true) . PHP_EOL;
                } else {
                    echo "User not found on message\n";
                }

            };
            $inner_tcp_worker->listen();
        };

        $ws_worker->onConnect = function($connection)
        {
            echo "connect\n";
            $connection->onWebSocketConnect = function($connection)
            {
                // при подключении нового пользователя сохраняем get-параметр, который же сами и передали со страницы сайта
                $user = $_GET['user'] ?? null;
                if ($user) {
                    $this->users[$user] = $connection;
                    // вместо get-параметра можно также использовать параметр из cookie, например $_COOKIE['PHPSESSID']
                    echo print_r($user, true);
                    echo "Connection connect\n";
                } else {
                    echo "User not found on connection start\n";
                }
            };
        };

        $ws_worker->onClose = function($connection)
        {
            echo "connect close\n";
            // удаляем параметр при отключении пользователя
            $user = array_search($connection, $this->users);
            unset($this->users[$user]);
            echo print_r($user, true);
            echo "Connection closed\n";
        };


        // Run worker
        Worker::runAll();
    }

}
