<?php
include "../wechat.class.php";

$options = array(
		'token'=>'XXXXXX', //填写你设定的key
		'encodingaeskey'=>'XXXXXX', //填写加密用的EncodingAESKey，如接口为明文模式可忽略
		'appid'=>'XXXXXX', //填写高级调用功能的app id
		'appsecret'=>'XXXXXX' //填写高级调用功能的密钥
	);
$weObj = new Wechat($options);

$order = $weObj->getOrderByID("13791169361138306965");
var_dump($order);

$order = $weObj->getOrderByFilter();
var_dump($order);
