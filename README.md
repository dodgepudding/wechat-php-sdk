wechat-php-sdk
==============

微信公众平台php开发包,细化各项接口操作,支持链式调用,欢迎Fork此项目  
weixin developer SDK.

使用范例
-------
wechat.class.php调用官方API，wechatext.class.php为非官方API，需要配置账户和密码，能实现主动点对点微信，此方式不保证长期有效  

* 基础API调用示例1：  
```
include "wechat.class.php"
$options = array(
		'token'=>'tokenaccesskey' //填写你设定的key
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
```

* 扩展包调用示例:  
```
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

License
-------
This is licensed under the GNU LGPL, version 2.1 or later.   
For details, see: http://creativecommons.org/licenses/LGPL/2.1/