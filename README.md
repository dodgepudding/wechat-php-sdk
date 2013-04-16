wechat-php-sdk
==============

微信公众平台php开发包,细化各项接口操作,支持链式调用,欢迎Fork此项目  
weixin developer SDK.

使用详解
-------
使用前需先打开微信帐号的开发模式，详细步骤请查看微信公众平台接口使用说明：  
http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97  

wechat.class.php调用官方API， 支持链式调用操作 ；  
wechatext.class.php为非官方API，需要配置公众平台账户和密码，能实现主动点对点微信，此方式不保证长期有效。  
wechatauth.class.php为通过微信二维码登陆微信的API, 能同时实现在网站同步登陆.

* 基础API调用示例：  
```
//test1.php
include "wechat.class.php"
$options = array(
		'token'=>'tokenaccesskey' //填写你设定的key
	);
$weObj = new Wechat($options);
$weObj->valid(); //注意, 应用验证通过后,可将此句注释掉, 但会降低网站安全性
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
```

* 扩展包调用示例: 
```
//test2.php 
	include("wechatext.class.php");
	
	function logdebug($text){
		file_put_contents('./data/log.txt',$text."\n",FILE_APPEND);		
	};
	
	$options = array(
		'account'=>'demo@domain.com',
		'password'=>'demo',
		'datapath'=>'./data/cookie_',
			'debug'=>true,
			'logcallback'=>'logdebug'	
	); 
	$wechat = new Wechatext($options);
	if ($wechat->checkValid()) {
		// 获取用户信息
		$data = $wechat->getInfo('3974255');
		// 主动发消息
		//$wechat->send('3974255','hello '.time());
		var_dump($data);
	}
```

* 微信二维码登陆示例:  
```
	include("../wechatauth.class.php");
	session_start();
	$sid  = session_id();
	$options = array(
		'account'=>$sid,
		'datapath'=>'../data/cookiecode_',
	); 
	$wechat = new Wechatauth($options);
	
	if (isset($_POST['code'])) {
		$logincode = $_POST['code'];
		$vres = $wechat->set_login_code($logincode)->verify_code();
		if ($vres===false) {
			$result = array('status'=>0);
		} else {
			$result = array('status'=>$vres);
			if ($vres==200) {
				$result['info'] = $wechat->get_login_info();
				$result['cookie'] = $wechat->get_login_cookie(true);
			}
		}
		
		die(json_encode($result));	
	}
	$logincode =  $wechat->get_login_code(); //获取授权码
	$qrimg = $wechat->get_code_image(); //待输出的二维码图片
```
HTML部分请看test/test3.php, 主要是不断ajax提交查询是否已经授权成功

License
-------
This is licensed under the GNU LGPL, version 2.1 or later.   
For details, see: http://creativecommons.org/licenses/LGPL/2.1/