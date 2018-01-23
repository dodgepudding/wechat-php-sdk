<?php
/**
 * 微信摇一摇周边上传素材测试
 * 
 */
	include("../wechat.class.php");

	$options = array(
		'appid'           => '', //填写高级调用功能的app id, 请在微信开发模式后台查询
        'appsecret'       => '', //填写高级调用功能的密钥
	);
	$weObj = new Wechat($options);
	$weObj->uploadShakeAroundMedia(array('media'=>'@'));