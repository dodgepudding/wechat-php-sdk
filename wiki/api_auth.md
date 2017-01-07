# wechatauth.class.php  

**此扩展类库已经不再更新，原因是官方开放平台对网站应用开放的有授权登陆接口，更标准，更好用。请查看：[微信开放平台](http://open.weixin.qq.com)**  

通过微信二维码登陆微信的API, 能实现第三方网站同步登陆, 首先程序分别通过get_login_code和get_code_image方法获取授权二维码图片, 然后利用微信手机客户端扫描二维码图片后将自动跳出授权页面, 用户点击授权后即可获取对应的用户资料和头像信息. 详细验证步骤请看test3.php例子.   

## 类主要方法:
 *  get_login_code() 获取登陆授权码, 通过授权码才能获取二维码  
 *  get_code_image($code='') 将上面获取的授权码转换为图片二维码  
 *  verify_code() 鉴定是否登陆成功,返回200为最终授权成功.  
 *  get_login_info() 鉴定成功后调用此方法即可获取用户基本信息  
 *  get_avatar($url) 获取用户头像图片数据  
 *  logout() 注销登陆  

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