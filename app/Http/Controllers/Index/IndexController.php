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

    public function server()
    {
        $ws = new Swoole\WebSocket\Server('0.0.0.0',9502);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {

});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    $info = json_decode($frame->data,true);
    if($info['type'] == 'login'){
        //swoole 提供的Redis客户端
        $redis = new Swoole\Coroutine\Redis();
        $key = "online_list";
        $redis->connect('127.0.0.1',6379);
        $list = $redis->get($key);
        $userlist = json_decode($list,true);
        if(empty($userlist)){
            $userlist = [];
        }
        
        $userlist[] = [
            'client_id'=>$frame->fd,
            'username'=>$info['con']
        ];
        $str = json_encode($userlist,JSON_UNESCAPED_UNICODE);
        $redis->set($key,$str);

        $message = [
            'type'=>'login',
            'is_me'=>1,
            'username'=>$info['con'],
             'online_list'=>$userlist
        ];
        $res = json_encode($message,JSON_UNESCAPED_UNICODE);
        $ws->push($frame->fd, $res);

        foreach ($userlist as $key => $value) {
            if($frame->fd != $value['client_id']){
                $message = [
                    'type'=>'login',
                    'is_me'=>0,
                    'username'=>$info['con'],
                    'online_list'=>$userlist
                ];
                $res = json_encode($message,JSON_UNESCAPED_UNICODE);
                $ws->push($value['client_id'], $res);
            }
        }
    }elseif($info['type']=='message'){
        $redis=new Swoole\Coroutine\Redis();
        $key="online_list";
        $redis->connect('127.0.0.1',6379);
        $list=$redis->get($key);
        $userlist=json_decode($list,true);
        //print_r($userlist);
        foreach($userlist as $k=>$v){
            if($v['client_id']==$frame->fd){
                $name=$v['username'];
            }
        }
        foreach($userlist as $key=>$value){
            if($value['client_id']==$frame->fd){
                $message=[
                    'type'=>'message',
                    'is_me'=>1,
                    'username'=>$name,
                    'message'=>$info['con'],
                    'online_list'=>$userlist
                ];
            }else{
                $message=[
                    'type'=>'message',
                    'is_me'=>0,
                    'username'=>$name,
                    'message'=>$info['con'],
                    'online_list'=>$userlist
                ];
            }
            $res=json_encode($message,JSON_UNESCAPED_UNICODE);
            $ws->push($value['client_id'], $res);
        }
    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    $redis=new Swoole\Coroutine\Redis();
    $key="online_list";
    $redis->connect('127.0.0.1',6379);
    $list=$redis->get($key);
    $userlist=json_decode($list,true);
    foreach($userlist as $key=>$value){
        if($value['client_id']==$fd){
            unset($userlist[$key]);
            $name=$value['username'];
        }
    }
    $str=json_encode($userlist);
    $redis->set($key,$str);
    foreach($userlist as $k=>$v){
        $message=[
            'type'=>'loginout',
            'is_me'=>0,
            'username'=>$name,
            'online_list'=>$userlist
        ];
        $res=json_encode($message,JSON_UNESCAPED_UNICODE);
        $ws->push($v['client_id'], $res);
    }
});

$ws->start();
    }
}
