wechat-php-sdk
==============

微信公众平台php开发包,细化各项接口操作,支持链式调用,欢迎Fork此项目  
weixin developer SDK.

使用详解
-------
使用前需先打开微信帐号的开发模式，详细步骤请查看微信公众平台接口使用说明：  
http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97  

1. wechat.class.php  
调用官方API，具有更灵活的消息分类响应方式，支持链式调用操作 ； 

2. wechatext.class.php  
非官方扩展API，需要配置公众平台账户和密码，能实现对已关注用户的点对点微信，此方式不保证长期有效。  
类方法里提及的用户id在接口返回结构里表述为FakeId, 属同一概念, 在下面wechatauth类里则表示为Uin, 用户id对应的微信号必须通过getInfo()方法通过返回数组的Username值获取, 但非关注关系用户资料不能获取.  
调用下列方法前必须经过login()方法和checkValid()验证方法才能获得调用权限. 有的账户无法通过登陆可能因为要求提供验证码, 可以手动登陆后把获取到的cookie写进程序存放cookie的文件解决.  
程序使用了经过修改的snoopy兼容式HTTP类方法, 在类似BAE/SAE云服务器上可能不能正常运行, 因为云服务的curl方法是经过重写的, 某些header参数如网站来源参数不被支持.  
类主要方法:
 *  send($id,$content) 向某用户id发送微信文字信息 
 *  sendNews($id,$msgid) 发送图文消息, 可通过getNewsList获取$msgid
 *  getNewsList($page,$pagesize) 获取图文信息列表 
 *  uploadFile($filepath,$type) 上传附件,包括图片/音频/视频
 *  getFileList($type,$page,$pagesize) 获取素材库文件列表
 *  sendImage($id,$fid) 发送图片消息
 *  sendAudio($id,$fid) 发送音频消息
 *  sendVideo($id,$fid) 发送视频消息 
 *  getInfo($id) 根据id获取用户资料,注: 非关注关系用户资料不能获取  
 *  getNewMsgNum($lastid) 获取从$lastid算起新消息的数目  
 *  getTopMsg() 获取最新一条消息的数据, 此方法获取的消息id可以作为检测新消息的$lastid依据  
 *  getMsg($lastid,$offset=0,$perpage=50,$day=0,$today=0,$star=0) 获取最新的消息列表, 列表将返回消息id, 用户id, 消息类型, 文字消息等参数  
 *  消息返回结构:  {"id":"消息id","type":"类型号(1为文字,2为图片,3为语音)","fileId":"0","hasReply":"0","fakeId":"用户uid","nickName":"昵称","dateTime":"时间戳","content":"文字内容"}   
 *  getMsgImage($msgid,$mode='large') 若消息type类型为2, 调用此方法获取图片数据  
 *  getMsgVoice($msgid) 若消息type类型为3, 调用此方法获取语音数据  

3. wechatauth.class.php  
通过微信二维码登陆微信的API, 能实现第三方网站同步登陆, 首先程序分别通过get_login_code和get_code_image方法获取授权二维码图片, 然后利用微信手机客户端扫描二维码图片后将自动跳出授权页面, 用户点击授权后即可获取对应的用户资料和头像信息. 详细验证步骤请看test3.php例子.   
类主要方法:
 *  get_login_code() 获取登陆授权码, 通过授权码才能获取二维码  
 *  get_code_image($code='') 将上面获取的授权码转换为图片二维码  
 *  verify_code() 鉴定是否登陆成功,返回200为最终授权成功.  
 *  get_login_cookie() 鉴定成功后调用此方法即可获取用户基本信息  
 *  sendNews($account,$title,$summary,$content,$pic,$srcurl='') 向一个微信账户发送图文信息  
 *  get_avatar($url) 获取用户头像图片数据  
 *  logout() 注销登陆  

官方Wechat调用示例：
--------  
```php
//test1.php
include "wechat.class.php";
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

扩展包Wechatext调用示例: 
--------
```php
//test2.php 
	include "wechatext.class.php";
	
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
		var_dump($data);
		// 获取最新一条消息
		$topmsg = $wechat->getTopMsg();
		var_dump($topmsg);
		// 主动回复消息
		if ($topmsg && $topmsg['hasReply']==0)
		$wechat->send($topmsg['fakeId'],'hi '.$topmsg['nickName'].',rev:'.$topmsg['content']);	
	}
```

微信二维码Wechatauth登陆示例: 
-------- 
```php
//test3.php
	include "../wechatauth.class.php";
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
HTML部分请看test/test3.php, 主要是定时ajax查询是否已经授权成功

License
-------
This is licensed under the GNU LGPL, version 2.1 or later.   
For details, see: http://creativecommons.org/licenses/LGPL/2.1/
