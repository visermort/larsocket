<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function ws()
    {
        return view('ws');
    }

    public function send()
    {
        $url = 'tcp://' . Config::get('workerman.tcp_url');
        $user = Request::get('user');
        $message = Request::get('message');

        if ($user && $message) {
            // connect to a local tcp-server
            try {
                $instance = stream_socket_client($url);
                // send message
                echo print_r(fwrite($instance, json_encode(['user' => $user, 'message' => $message]) . "\n"), true);

            } catch (\Exception $e) {
                echo($e->getMessage());
            }
        } else {
            echo 'user or message not found';
        }

    }
}
