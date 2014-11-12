wechat-php-sdk
==============

微信公众平台php开发包,细化各项接口操作,支持链式调用,欢迎Fork此项目  
weixin developer SDK.
项目地址：**https://github.com/dodgepudding/wechat-php-sdk**  
项目blog：**http://binsee.github.io/wechat-php-sdk**  

## 使用详解
使用前需先打开微信帐号的开发模式，详细步骤请查看微信公众平台接口使用说明：  
微信公众平台： http://mp.weixin.qq.com/wiki/
微信企业平台： http://qy.weixin.qq.com/wiki/

微信支付接入文档：
https://mp.weixin.qq.com/cgi-bin/readtemplate?t=business/course2_tmpl&lang=zh_CN

微信多客服：http://dkf.qq.com


## 目录 
> **[wechat.class.php 官方API类库](#1-wechatclassphp-官方api类库)**  
> **[wechatext.class.php 非官方扩展API](#2-wechatextclassphp-非官方扩展api)**  
> **[wechatauth.class.php 授权登陆](#3-wechatauthclassphp-授权登陆)**  
> **[wechat.js 内嵌JS](#4-wechatjs-内嵌js)**  
> **[errCode.php 全局返回码类](#5-errcodephp-全局返回码类)**  
> **[qywechat.class.php 企业号API类库](#6-qywechatclassphp-企业号api类库)**  
> **[调用示例](#调用示例)**  

----------

## 1. wechat.class.php 官方API类库
调用官方API，具有更灵活的消息分类响应方式，支持链式调用操作 ； 

### 主要功能 
- 接入验证 （初级权限）
- 自动回复（文本、图片、语音、视频、音乐、图文）（初级权限）
- 菜单操作（查询、创建、删除）（菜单权限）
- 客服消息（文本、图片、语音、视频、音乐、图文）（认证权限）
- 二维码（创建临时、永久二维码，获取二维码URL）（认证权限）
- 长链接转短链接接口（认证权限）
- 分组操作（查询、创建、修改、移动用户到分组）（认证权限）
- 网页授权（基本授权，用户信息授权）（认证权限）
- 用户信息（查询用户基本信息、获取关注者列表）（认证权限）
- 多客服功能（认证权限）
- 媒体文件（上传、获取）（认证权限） 
- 调用地址组件 （支付权限） 
- 生成订单签名数据 （支付权限） 
- 订单成功回调 （支付权限） 
- 发货通知 （支付权限） 
- 支付订单查询 （支付权限） 
- 模板消息（支付权限） 
- 语义理解（认证权限） 
- 获取微信服务器IP列表（认证权限） 


### 初始化动作 
```php
 $options = array(
	'token'=>'tokenaccesskey', //填写你设定的key
	'encodingaeskey'=>'encodingaeskey', //填写加密用的EncodingAESKey
	'appid'=>'wxdk1234567890', //填写高级调用功能的app id, 请在微信开发模式后台查询
	'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
	'partnerid'=>'88888888', //财付通商户身份标识，支付权限专用，没有可不填
	'partnerkey'=>'', //财付通商户权限密钥Key，支付权限专用
	'paysignkey'=>'' //商户签名密钥Key，支付权限专用
	);
 $weObj = new Wechat($options); //创建实例对象
 //TODO：调用$weObj各实例方法

```

### 新增Auth高级权限类方法:   
 *  checkAuth($appid,$appsecret,$token) 此处传入公众后台高级接口提供的appid和appsecret, 或者手动指定$token为access_token。函数将返回access_token操作令牌
 *  createMenu($data) 创建菜单 $data菜单结构详见 **[自定义菜单创建接口](http://mp.weixin.qq.com/wiki/index.php?title=自定义菜单创建接口)**
 *  getServerIp() 获取微信服务器IP地址列表 返回数组array('127.0.0.1','127.0.0.1')
 *  getMenu() 获取菜单 
 *  deleteMenu() 删除菜单 
 *  uploadMedia($data, $type) 上传多媒体文件
 *  getMedia() 获取接收到的音频、视频媒体文件 
 *  uploadArticles($data) 上传图文消息素材
 *  sendMassMessage($data) 高级群发消息
 *  sendGroupMassMessage($data) 高级群发消息（分组群发）
 *  deleteMassMessage() 删除群发图文消息
 *  getQRCode($scene_id,$type=0,$expire=1800) 获取推广二维码ticket字串 
 *  getQRUrl($ticket) 获取二维码图片地址
 *  getShortUrl($long_url) 长链接转短链接接口
 *  getUserList($next_openid) 批量获取关注用户列表 
 *  getUserInfo($openid) 获取关注者详细信息 
 *  updateUserRemark($openid,$remark) 设置用户备注名
 *  getGroup() 获取用户分组列表 
 *  getUserGroup($openid) 获取用户所在分组
 *  createGroup($name) 新增自定分组 
 *  updateGroup($groupid,$name) 更改分组名称 
 *  updateGroupMembers($groupid,$openid) 移动用户分组  
 *  sendCustomMessage($data) 发送客服消息  
 *  getOauthRedirect($callback,$state,$scope) 获取网页授权oAuth跳转地址  
 *  getOauthAccessToken() 通过回调的code获取网页授权access_token  
 *  getOauthRefreshToken($refresh_token) 通过refresh_token对access_token续期  
 *  getOauthUserinfo($access_token,$openid) 通过网页授权的access_token获取用户资料  
 *  getOauthAuth($access_token,$openid)  检验授权凭证access_token是否有效
 *  getSignature($arrdata,'sha1') 生成签名字串  
 *  generateNonceStr($length) 获取随机字串  
 *  createPackage($out_trade_no,$body,$total_fee,$notify_url,$spbill_create_ip,$fee_type=1,$bank_type="WX",$input_charset="UTF-8",$time_start="",$time_expire="",$transport_fee="",$product_fee="",$goods_tag="",$attach="") 生成订单package字符串  
 *  getPaySign($package, $timeStamp, $nonceStr) 支付签名(paySign)生成方法  
 *  checkOrderSignature($orderxml='') 回调通知签名验证  
 *  sendPayDeliverNotify($openid,$transid,$out_trade_no,$status=1,$msg='ok') 发货通知  
 *  getPayOrder($out_trade_no) 查询订单信息  
 *  getAddrSign($url, $timeStamp, $nonceStr, $user_token='') 获取收货地址JS的签名
 *  sendTemplateMessage($data) 发送模板消息
 *  getCustomServiceMessage($data) 获取多客服会话记录
 *  transfer_customer_service($customer_account) 转发多客服消息
 *  getCustomServiceKFlist() 获取多客服客服基本信息
 *  getCustomServiceOnlineKFlist() 获取多客服在线客服接待信息
 *  querySemantic($uid,$query,$category,$latitude=0,$longitude=0,$city="",$region="") 语义理解接口 参数含义及返回的json内容请查看 **[微信语义理解接口](http://mp.weixin.qq.com/wiki/index.php?title=语义理解)**
 
 
## 2. wechatext.class.php 非官方扩展API  
非官方扩展API，需要配置公众平台账户和密码，能实现对已关注用户的点对点微信，此方式不保证长期有效。  
类方法里提及的用户id在接口返回结构里表述为FakeId, 属同一概念, 在下面wechatauth类里则表示为Uin, 用户id对应的微信号必须通过getInfo()方法通过返回数组的Username值获取, 但非关注关系用户资料不能获取.  
调用下列方法前必须经过login()方法和checkValid()验证方法才能获得调用权限. 有的账户无法通过登陆可能因为要求提供验证码, 可以手动登陆后把获取到的cookie写进程序存放cookie的文件解决.  
程序使用了经过修改的snoopy兼容式HTTP类方法, 在类似BAE/SAE云服务器上可能不能正常运行, 因为云服务的curl方法是经过重写的, 某些header参数如网站来源参数不被支持.  

### 类主要方法:
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

## 3. wechatauth.class.php 授权登陆
通过微信二维码登陆微信的API, 能实现第三方网站同步登陆, 首先程序分别通过get_login_code和get_code_image方法获取授权二维码图片, 然后利用微信手机客户端扫描二维码图片后将自动跳出授权页面, 用户点击授权后即可获取对应的用户资料和头像信息. 详细验证步骤请看test3.php例子.   
### 类主要方法:
 *  get_login_code() 获取登陆授权码, 通过授权码才能获取二维码  
 *  get_code_image($code='') 将上面获取的授权码转换为图片二维码  
 *  verify_code() 鉴定是否登陆成功,返回200为最终授权成功.  
 *  get_login_info() 鉴定成功后调用此方法即可获取用户基本信息  
 *  get_avatar($url) 获取用户头像图片数据  
 *  logout() 注销登陆  

## 4. wechat.js 内嵌JS
### 微信内嵌网页特殊功能js调用：
 * WeixinJS.hideOptionMenu() 隐藏右上角按钮
 * WeixinJS.showOptionMenu() 显示右上角按钮
 * WeixinJS.hideToolbar() 隐藏工具栏
 * WeixinJS.showToolbar() 显示工具栏
 * WeixinJS.getNetworkType() 获取网络状态
 * WeixinJS.closeWindow() 关闭窗口
 * WeixinJS.scanQRCode() 扫描二维码
 * WeixinJS.openUrlByExtBrowser(url) 使用浏览器打开网址
 * WeixinJS.jumpToBizProfile(username) 跳转到指定公众账号页面
 * WeixinJS.sendEmail(title,content) 发送邮件
 * WeixinJS.openProductView(latitude,longitude,name,address,scale,infoUrl) 查看地图
 * WeixinJS.addContact(username) 添加微信账号
 * WeixinJS.imagePreview(urls,current) 调出微信内图片预览
 * WeixinJS.payCallback(appId,package,timeStamp,nonceStr,signType,paySign,callback) 微信JsApi支付接口
 * WeixinJS.editAddress(appId,addrSign,timeStamp,nonceStr,callback) 微信JsApi支付接口
 * 通过定义全局变量dataForWeixin配置触发分享的内容：
 ```javascript
 var dataForWeixin={
	   appId:"",
	   MsgImg:"消息图片路径",
	   TLImg:"时间线图路径",
	   url:"分享url路径",
	   title:"标题",
	   desc:"描述",
	   fakeid:"",
	   callback:function(){}
	};
 ```

## 5. errCode.php 全局返回码类
当调用API接口失败时，可以用此类来换取失败原因的中文说明。
### 使用方法：
```php
include "errCode.php";  //或 qyerrCode.php

$ret=ErrCode::getErrText(48001); //错误码可以通过公众号类库的公开变量errCode得到

if ($ret) 
	echo $ret;
else 
    echo "未找到对应的内容";

```

## 6. qywechat.class.php 企业号API类库 
调用官方API，具有更灵活的消息分类响应方式，支持链式调用操作 ； 

### 主要功能 
- 接入验证 （初级权限）
- 自动回复（文本、图片、语音、视频、音乐、图文）
- 菜单操作（查询、创建、删除）
- 部门管理（创建、更新、删除、获取部门列表）
- 成员管理（创建、更新、删除、获取成员信息，获取部门成员列表）
- 标签管理（创建、更新、删除、获取成员、添加成员、删除成员,获取标签列表）
- 媒体文件管理（上传、获取）
- 二次验证
- OAuth2（生成授权url、获取成员信息）


### 初始化动作 
```php
$options = array(
  'token'=>'tokenaccesskey', //填写应用接口的Token
  'encodingaeskey'=>'encodingaeskey', //填写加密用的EncodingAESKey
  'appid'=>'wxdk1234567890', //填写高级调用功能的app id
  'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
  'agentid'=>'1', //应用的id
  'debug'=>false, //调试开关
  '_logcallback'=>'logg', //调试输出方法，需要有一个string类型的参数
);
 $weObj = new Wechat($options); //创建实例对象
 //TODO：调用$weObj各实例方法

```

### 被动接口方法:   
* valid() 验证连接
* 
* getRev() 获取微信服务器发来信息(不返回结果)
* getRevData() 返回微信服务器发来的信息（数组）
* getRevPostXml() 返回微信服务器发来的原始加密xml信息
* getRevFrom()  返回消息发送者的userid
* getRevTo()  返回消息接收者的id（即公众号id，一般与等同appid一致）
* getRevAgentID() 返回接收消息的应用id
* getRevType() 返回接收消息的类型
* getRevID() 返回消息id
* getRevCtime() 返回消息发送事件
* getRevContent() 返回消息内容正文（文本型消息）
* getRevPic() 返回图片信息（图片型信息） 返回数组{'mediaid'=>'','picurl'=>''}
* getRevGeo() 返回地理位置（位置型信息） 返回数组{'x'=>'','y'=>'','scale'=>'','label'=>''}
* getRevEventGeo() 返回事件地理位置（事件型信息） 返回数组{'x'=>'','y'=>'','precision'=>''}
* getRevEvent() 返回事件类型（事件型信息） 返回数组{'event'=>'','key'=>''}
* getRevScanInfo() 获取自定义菜单的扫码推事件信息，事件类型为`scancode_push`或`scancode_waitmsg` 返回数组array ('ScanType'=>'qrcode','ScanResult'=>'123123')
* getRevSendPicsInfo() 获取自定义菜单的图片发送事件信息,事件类型为`pic_sysphoto`或`pic_photo_or_album`或`pic_weixin` 数组结构见php文件内方法说明
* getRevSendGeoInfo() 获取自定义菜单的地理位置选择器事件推送，事件类型为`location_select` 数组结构见php文件内方法说明
* getRevVoice() 返回语音信息（语音型信息） 返回数组{'mediaid'=>'','format'=>''}
* getRevVoice() 返回语音信息（语音型信息） 返回数组{'mediaid'=>'','format'=>''}
* getRevVideo() 返回视频信息（视频型信息） 返回数组{'mediaid'=>'','thumbmediaid'=>''}
* 
* text($text) 设置文本型消息，参数：文本内容
* image($mediaid) 设置图片型消息，参数：图片的media_id
* voice($mediaid) 设置语音型消息，参数：语音的media_id
* video($mediaid='',$title,$description) 设置视频型消息，参数：视频的media_id、标题、摘要
* news($newsData) 设置图文型消息，参数：数组。数组结构见php文件内方法说明
* image($mediaid) 设置图片型消息，参数：图片的media_id
* Message($msg = '',$append = false) 设置发送的消息（一般不需要调用这个方法）
* reply() 将已经设置好的消息，回复给微信服务器

### 主动接口方法：
* checkAuth($appid='',$appsecret='',$token='') 通用auth验证方法,也用来换取ACCESS_TOKEN 。仅在需要手动指定access_token时才用`$token`
* resetAuth($appid='') 清除记录的ACCESS_TOKEN
* createMenu($data,$agentid='') 创建菜单,参数:菜单内容数组,要创建菜单应用id
* getMenu($agentid='') 获取菜单内容,参数:要获取菜单内容的应用id
* deleteMenu($agentid='') 删除菜单,参数:要删除菜单的应用id
* uploadMedia($data, $type) 上传媒体文件,参数请看php文件内方法说明
* getMedia($media_id) 根据媒体文件ID获取媒体文件,参数:媒体id
* createDepartment($data) 创建部门,参数: array("name"=>"邮箱产品组","parentid"=>"1","order" =>  "1")
* updateDepartment($data) 更新部门,参数: array("id"=>"1"，"name"=>"邮箱产品组","parentid"=>"1","order" =>  "1")
* deleteDepartment($id) 删除部门,参数：要删除的部门id
* moveDepartment($data) 移动部门,参数：array("department_id" => "5","to_parentid" => "2","to_position" => "1")
* getDepartment() 获取部门列表，返回部门数组。其中department部门列表数据。以部门的order字段从小到大排列
* createUser($data) 创建成员，参数请看php文件内方法说明
* updateUser($data) 更新成员，参数请看php文件内方法说明
* deleteUser($userid) 删除成员，参数：员工UserID
* getUserInfo($userid) 获取成员信息，参数：员工UserID
* getUserList($department_id,$fetch_child=0,$status=0) 获取部门成员，参数：部门id，是否递归获取子部门，获取类型。
> 0获取全部员工，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
* getUserId($code,$agentid) 根据code获取员工UserID与手机设备号，参数：Oauth2.0或者二次验证返回的code值，跳转链接时所在的企业应用ID
* createTag($data) 创建标签，参数：array("tagname" => "UI")
* updateTag($data) 更新标签，参数：array("tagid" => "1","tagname" => "UI")
* deleteTag($tagid) 删除标签，参数：标签TagID
* getTag($tagid) 获取标签成员，参数：标签TagID
* addTagUser($data) 增加标签成员，参数请看php文件内方法说明
* delTagUser($data) 删除标签成员，参数请看php文件内方法说明
* getTagList() 获取标签列表，返回标签数组
* sendMessage($data) 主动发送信息接口，参数请看php文件内方法说明
* authSucc($userid) 二次验证，参数： 员工UserID
* getOauthRedirect($callback,$state='STATE',$scope='snsapi_base') 组合授权跳转接口url
  
  
# 调用示例
----------

## 官方Wechat调用示例：
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

## 微信二维码Wechatauth登陆示例: 
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

## 企业号API类库调用示例：
可参考**test**目录下的**qydemo.php**
```php
include "wechat.class.php";
$options = array(
        'token'=>'9Ixxxxxxx',	//填写应用接口的Token
        'encodingaeskey'=>'d4o9WVg8sxxxxxxxxxxxxxxxxxxxxxx',//填写加密用的EncodingAESKey
        'appid'=>'wxa07979baxxxxxxxx',	//填写高级调用功能的appid
);
$weObj = new Wechat($options);
$weObj->valid(); //注意, 企业号与普通公众号不同，必须打开验证，不要注释掉
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

License
-------
This is licensed under the GNU LGPL, version 2.1 or later.   
For details, see: http://creativecommons.org/licenses/LGPL/2.1/
