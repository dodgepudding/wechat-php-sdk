<?php
/**
 * 微信扩展接口测试
 */
	include("../wechatext.class.php");
	
	function logdebug($text){
		file_put_contents('../data/log.txt',$text."\n",FILE_APPEND);		
	};
	
	$options = array(
		'account'=>'demo@domain.com',
		'password'=>'demo',
		'datapath'=>'../data/cookie_',
			'debug'=>true,
			'logcallback'=>'logdebug'	
	); 
	$wechat = new Wechatext($options);
	if ($wechat->checkValid()) {
		// 获取用户信息
		$data = $wechat->getInfo('3974255');
		var_dump($data);
		// 获取最新一条消息
		$topmsg = $wechat->getTopMsg();
		var_dump($topmsg);
		// 主动回复消息
		if ($topmsg && $topmsg['hasReply']==0)
		$wechat->send($topmsg['fakeId'],'hi '.$topmsg['nickName'].',rev:'.$topmsg['content']);	
	} else {
		echo "login error";
	}