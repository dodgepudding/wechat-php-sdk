# wechatext.class.php

**此扩展类库已经不再更新，原因是官方对公众号开放了众多接口，此类库继续维护的意义不大**  

非官方扩展API，需要配置公众平台账户和密码，能实现对已关注用户的点对点微信，此方式不保证长期有效。  
类方法里提及的用户id在接口返回结构里表述为FakeId, 属同一概念, 在下面wechatauth类里则表示为Uin, 用户id对应的微信号必须通过getInfo()方法通过返回数组的Username值获取, 但非关注关系用户资料不能获取.  
调用下列方法前必须经过login()方法和checkValid()验证方法才能获得调用权限. 有的账户无法通过登陆可能因为要求提供验证码, 可以手动登陆后把获取到的cookie写进程序存放cookie的文件解决.  
程序使用了经过修改的snoopy兼容式HTTP类方法, 在类似BAE/SAE云服务器上可能不能正常运行, 因为云服务的curl方法是经过重写的, 某些header参数如网站来源参数不被支持.  

## 类主要方法:
 *  send($id,$content) 向某用户id发送微信文字信息 
 *  sendNews($id,$msgid) 发送图文消息, 可通过getNewsList获取$msgid
 *  getUserList($page,$pagesize,$groupid) 获取用户信息
 *  getGroupList($page,$pagesize) 获取群组信息
 *  getNewsList($page,$pagesize) 获取图文信息列表 
 *  uploadFile($filepath,$type) 上传附件,包括图片/音频/视频/缩略图
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


## 扩展包Wechatext调用示例:
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
		if ($topmsg && $topmsg['has_reply']==0)
		$wechat->send($topmsg['fakeid'],'hi '.$topmsg['nick_name'].',rev:'.$topmsg['content']);	
	}
```