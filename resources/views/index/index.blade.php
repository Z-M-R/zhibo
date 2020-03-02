<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>聊天室</title>
    <meta http-equiv="x-ua-compatible" content="IE=edge" >
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no"/>
    <link rel="stylesheet" href="https://g.alicdn.com/de/prismplayer/2.8.2/skins/default/aliplayer-min.css" />
     <script charset="utf-8" type="text/javascript" src="https://g.alicdn.com/de/prismplayer/2.8.2/aliplayer-min.js"></script>
</head>
<body>
    <div style="float:right;width=30%;height:600px;border: 1px solid red;">
        <div class="onlinelist" style="float:right;border: 1px solid blue; width:80px;height:600px;"><p style="text-align:center;color:red;">在线列表</p></div>
        <div style="float:left;border: 1px solid red;">
            <div style="width:200px;height:400px; border: 1px solid black;overflow:auto" id="list"></div>
            <input type="text" id="message" >
            <input type="button" value="发送" id="btn">
            <img src="./bq.png" alt="添加表情" style="width:30px;height:30px;margin-top:10px;" id="bq">
            <div id="bqlist" style="width:200px;height:auto"></div>
        </div>
    </div>
    <div class="prism-player" id="player-con" style="left"></div>
</body>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script>
    //用户名登陆以后获取用户名
    var username = prompt('请输入用户名');
    var ws = new WebSocket("ws://123.56.158.49:9502");
    ws.onopen = function () {
        var message = '{"type":"login","con":"'+username+'"}';
        // console.log(message);
        ws.send(message);
    }
    ws.onmessage = function (res) {
        var data = JSON.parse(res.data);
        //console.log(data);
        if(data.is_me == 1 && data.type=='login'){
            var content = "<p style='text-align:center'>尊敬的用户："+data.username+"欢迎您的来到</p>"
        }else if(data.is_me == 0 && data.type=='login'){
            var content = "<p style='text-align:center'>系统消息："+data.username+"上线了</p>"
        }else if(data.is_me == 1 && data.type=='message'){
            var content="<div align='right'><p style='margin-left:20px;'>来自您的消息</p></p><p style='border:1px solid #ff0000;margin-right:20px;width:40%;height:auto;border-radius:inherit;background-color:#00FFFF'>"+data.message+"</p></div>";
        }else if(data.is_me == 0 && data.type=='message'){
            var content="<div align='left'><p style='margin-left:20px;'>来自"+data.username+"的消息</p></p><p style='border:1px solid #ff0000;margin-left:20px;width:40%;height:auto;border-radius:inherit;background-color:#00FFFF'>"+data.message+"</p></div>";
        }else if(data.is_me == 0 && data.type=='loginout'){
            var content="<p style='text-align:center'>系统消息："+data.username+"离开了直播间</p>"
        }
        var list='在线用户列表';
        for(var i in data.online_list){
            list +="<p>"+data.online_list[i].username+"</p>"
        }
        $(".onlinelist").html(list);
        $("#list").append(content);
        // $("#list").text(res.data);
    }

    $(document).on('click',"#btn",function(){
        var con=$("#message").val();
        var message = '{"type":"message","con":"'+con+'"}';
        ws.send(message);
    })

    $(document).on('click','#bq',function(){
        $.ajax({
            url:'./bq.php',
            dataType:'json',
            success:function(res){
                //如果返回值是纯黑色字体   是字符串
                //var data=evel("("+res+")")  使用这个函数进行转换
                var img='';
                for(var i in res){
                    //console.log(res[i]);
                    img +="<img class='bqimg' src='./bq/"+res[i]+"' style='width:50px;height:50px;'>";
                }
                $("#bqlist").html(img);
            }
        })
    })

    $(document).on('click',".bqimg",function(){
        var src=$(this).attr("src");
        var con="<img src='"+src+"' style='width:80px;height:80px;'>";
        var message = '{"type":"message","con":"'+con+'"}';
        ws.send(message);
    })

    var player = new Aliplayer({
            "id": "player-con",
            "source": "rtmp://youke.zmrzzj.com/zhang/zhang?auth_key=1583114584-0-0-3bc939c9c56544d1e6fdda55a91275b7",
            "width": "70%",
            "height": "600px",
            "autoplay": true,
            "isLive": true,
            "rePlay": false,
            "playsinline": true,
            "preload": true,
            "controlBarVisibility": "hover",
            "useH5Prism": true
        }, function (player) {
            console.log("The player is created");
        }
    );
</script>
</html>