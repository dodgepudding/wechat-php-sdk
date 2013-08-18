<?php
/**
 * 微信公共接口测试
 * 
 */
	include("../wechat.class.php");
	
	function logdebug($text){
		file_put_contents('../data/log.txt',$text."\n",FILE_APPEND);		
	};
	$options = array(
		'token'=>'tokenaccesskey', //填写你设定的key
			'debug'=>true,
			'logcallback'=>'logdebug'
	);
	$weObj = new Wechat($options);
	$weObj->valid();
	$type = $weObj->getRev()->getRevType();
	switch($type) {
		case Wechat::MSGTYPE_TEXT:
				$weObj->text("hello, I'm wechat")->reply();
				exit;
				break;
		case Wechat::MSGTYPE_EVENT:
				break;
		case Wechat::MSGTYPE_IMAGE:
				break;
		default:
				$weObj->text("help info")->reply();
	}
