<?php
/**
 * 微信oAuth认证
 */
include("../wechat.class.php");
class wxauth {
	private $options;
	public $openid;
	public $wxuser;
	public $errcode = 0;
	public $errmsg = 'success';
	
	public function __construct($options){
		$this->options = $options;
		$this->wxoauth();
	}
	
	public function wxoauth(){
		$scope = 'snsapi_base';
		$code = isset($_GET['code'])?$_GET['code']:'';
		$options = array(
				'token'=>$this->options["token"], //填写你设定的key
				'appid'=>$this->options["appid"], //填写高级调用功能的app id
				'appsecret'=>$this->options["appsecret"] //填写高级调用功能的密钥
		);
		$we_obj = new Wechat($options);
		//通过code获取用户信息
		if($code)
		{
			$json = $we_obj->getOauthAccessToken(); //通过code获取Access Token
			if (!$json)
			{
				//删除code重新获取授权
				parse_str($_SERVER['QUERY_STRING'],$QUERY_STRING);
				unset($QUERY_STRING['code']);
				unset($QUERY_STRING['state']);
				$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.http_build_query($QUERY_STRING);
				$oauth_url = $we_obj->getOauthRedirect($url,"wxbase",$scope);
				header('Location: ' . $oauth_url);
				die('获取用户授权失败(errCode:'.$we_obj->errCode.')(errMsg:'.$we_obj->errMsg.')');
			}
			else
			{
				$this->openid = $json["openid"];
				$userinfo = $we_obj->getUserInfo($this->openid);
				if($userinfo)
				{
					if($userinfo['subscribe']==0)
					{
						$this->errcode = 43004;
						$this->errmsg = '用户未关注公众账号';
						return false;
					}
					$this->wxuser = $userinfo;
				}
				else
				{
					$this->errcode = $we_obj->errCode;
					$this->errmsg = $we_obj->errMsg;
					return false;
				}
				return $this->openid;
			}
		}
		
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$oauth_url = $we_obj->getOauthRedirect($url,"wxbase",$scope);
		header('Location: ' . $oauth_url);
	}
}

$options = array(
		'token'=>'tokenaccesskey', //填写你设定的key
		'appid'=>'wxdk1234567890', //填写高级调用功能的app id, 请在微信开发模式后台查询
		'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
);

$auth = new wxauth($options);
$wxuser = $auth->wxuser;
if($wxuser['subscribe']==1)
{
	$url='login.php';
}
else
{
	$url='messageErr.php';
}
require('temp/'.$url);
exit;