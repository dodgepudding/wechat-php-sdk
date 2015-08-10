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
		//获取分组列表
		$grouplist = $wechat->getGroupList();
		var_dump($grouplist);
		//获取用户列表
		$userlist = $wechat->getUserlist(0,10);
		var_dump($userlist);
		$user = $userlist[0];
		// 获取用户信息
		$userdata = $wechat->getInfo($user['id']);
		var_dump($userdata);
		// 获取已保存的图文消息
		$newslist = $wechat->getNewsList(0,10);
		var_dump($newslist);
		//获取用户最新消息
		$topmsg = $wechat->getTopMsg();
		var_dump($topmsg);
		$msglist = $wechat->getMsg();
		var_dump($msglist);
		// 主动回复消息
		if ($topmsg && $topmsg['has_reply']==0){
		    $wechat->send($user['id'],'hi '.$topmsg['nick_name'].',rev:'.$topmsg['content']);
		    $content = '这是一条Wechatext发出的测试微信';
		    $imgdata = file_get_contents('http://github.global.ssl.fastly.net/images/modules/dashboard/bootcamp/octocat_fork.png');
		    $img = '../data/send.png';
		    file_put_contents($img,$imgdata);
		    //上传图片
		    $fileid = $wechat->uploadFile($img);
		    echo 'fileid:'.$fileid;
		    //if ($fileid) $re = $wechat->sendImage($user['id'],$fileid);
		    //发送图文信息
		    $re = $wechat->sendPreview($userdata['user_name'],$content,$content,$content,$fileid,'http://github.com/dodgepudding/wechat-php-sdk');
		    var_dump($re);
		    //发送视频
		    //$re = $wechat->sendVideo($user['id'],$fileid);
			$re = $wechat->getFileList(2,0,10);
			var_dump($re);
		} else {
			echo 'no top msg';
		}	
	} else {
		echo "login error";
	}