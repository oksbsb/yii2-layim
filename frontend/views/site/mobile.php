<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use frontend\assets\LayuiMobileAsset;

LayuiMobileAsset::register($this);
$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;

?>

<script>
    layui.use('mobile', function(){
        var mobile = layui.mobile
            ,layim = mobile.layim;
        //基础配置
        layim.config({
            init: {
                //设置我的基础信息
                mine: {
                    "username": "<?=$model->username?>" //我的昵称
                    ,"id": <?=$model->id?> //我的ID
                    ,"avatar": "<?=$model->avatar?>" //我的头像
                }
            }
            //上传图片接口（返回的数据格式见下文）
            ,uploadImage: {
                url: '/file/upload' //接口地址
                ,type: 'post' //默认post
            }

            //扩展工具栏，下文会做进一步介绍（如果无需扩展，剔除该项即可）
            ,tool: [{
                alias: 'code' //工具别名
                ,title: '代码' //工具名称
                ,icon: '&#xe64e;' //工具图标，参考图标文档
            }]
            ,brief: true //是否简约模式（默认false，如果只用到在线客服，且不想显示主面板，可以设置 true）
            ,title: 'mi' //主面板最小化后显示的名称
            ,maxLength: 3000 //最长发送的字符长度，默认3000
            ,isfriend: true //是否开启好友（默认true，即开启）
            ,isgroup: false //是否开启群组（默认true，即开启）
            ,right: '0px' //默认0px，用于设定主面板右偏移量。该参数可避免遮盖你页面右下角已经的bar。
            ,chatLog: '' //聊天记录地址（如果未填则不显示）
            ,find: '' //查找好友/群的地址（如果未填则不显示）
            ,copyright: true //是否授权，如果通过官网捐赠获得LayIM，此处可填true
        });
        //创建一个会话
        layim.chat({
            id: <?=$to->id?>
            ,name: '<?=$to->username?>'
            ,type: 'friend' //friend、group等字符，如果是group，则创建的是群聊
            ,avatar: '<?=$to->avatar?>'
        });
//layim建立就绪
//        layim.on('ready', function(res){

//        });


        //建立WebSocket通讯
        var socket = new WebSocket('ws://127.0.0.1:8282');
        //连接成功时触发
        socket.onopen = function(){
            // 登录
            var login_data = '{"type":"init","id":"<?= $model->getAttribute('id')?>","username":"<?= $model->getAttribute('username')?>","avatar":"<?= $model->getAttribute('avatar')?>","sign":"dffffd"}';
            socket.send( login_data );
            console.log("websocket握手成功!");
        };

        //监听收到的消息
        socket.onmessage = function(res){
            console.log(res.data);
            var data = eval("("+res.data+")");
            switch(data['message_type']){
                // 服务端ping客户端
                case 'ping':
                    socket.send('{"type":"ping"}');
                    break;
                // 登录 更新用户列表
                case 'init':
                    console.log(data['id']+"登录成功");
                    //layim.getMessage(res.data); //res.data即你发送消息传递的数据（阅读：监听发送的消息）
                    break;
                // 检测聊天数据
                case 'chatMessage':
                    console.log(data.data);
                    layim.getMessage(data.data);
                    break;
                // 用户退出 更新用户列表
                case 'logout':
                    break;
                //聊天还有不在线
                case 'ctUserOutline':
                    console.log('11111');
                    //layer.msg('好友不在线', {'time' : 1000});
                    break;

            }
        };
        socket.onclose = function(){
            // 登录
            console.log("websocket断开!");
        };
        layim.on('sendMessage', function(res){
            console.log(res);
            // 发送消息
            var mine = JSON.stringify(res.mine);
            var to = JSON.stringify(res.to);
            var login_data = '{"type":"chatMessage","data":{"mine":'+mine+', "to":'+to+'}}';
            socket.send( login_data );
        });
        layim.on('chatlog', function(data){
            console.log(data.elem); //获取会话中的DOM容器
            console.log(data.id); //获取会话id
            console.log(data.type); //获取会话类型
            //do something
        });
        layim.on('tool(code)', function(insert){ //事件中的tool为固定字符，而code则为过滤器，对应的是工具别名（alias）
            layer.open({
                title: '插入代码'
                ,formType: 2
                ,shade: 0
            }, function(text, index){
                layer.close(index);
                insert('[pre class=layui-code]' + text + '[/pre]'); //将内容插入到编辑器，主要由insert完成
            });
        });
    });
</script>
