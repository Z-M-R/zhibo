<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Swoole\WebSocket\Server;

class IndexController extends Controller
{
    //
    public function index()
    {
        $this->render("index/index");
    }
    
    public function login()
    {
        echo 123;
    }
}
