# qywechat.class.php

## 企业号API类库
调用官方API，具有更灵活的消息分类响应方式，支持链式调用操作 ； 

## 主要功能 
- 接入验证 （初级权限）
- 自动回复（文本、图片、语音、视频、音乐、图文）
- 菜单操作（查询、创建、删除）
- 部门管理（创建、更新、删除、获取列表）
- 成员管理（创建、更新、删除、获取成员信息，获取部门成员列表）
- 标签管理（创建、更新、删除、获取成员、添加成员、删除成员）
- 媒体文件管理（上传、获取）
- 二次验证
- OAuth2（生成授权url、获取成员信息）


## 初始化动作 
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

## 被动接口方法:   
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

## 主动接口方法：
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






## 企业号API类库调用示例：
-------- 
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
