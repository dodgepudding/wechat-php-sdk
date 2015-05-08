<?php
/**
 * 微信二维码登陆测试
 */
	include("../wechatauth.class.php");
	session_start();
	function logdebug($text){
		file_put_contents('../log/logwechat.txt',$text."\n",FILE_APPEND);		
	};
	$sid  = session_id();
	$options = array(
		'account'=>$sid,
		'datapath'=>'../log/cookiecode_',
			'debug'=>true,
			'logcallback'=>'logdebug'	
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
	$logincode =  $wechat->get_login_code();
	$qrimg = $wechat->get_code_image();
	
?>

<!DOCTYPE html>
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<title>微信二维码登陆接口</title>
</head>
<body>
<h2>微信里扫描以下二维码实现登陆</h2>
<div id="content">
<img src="<?php echo $qrimg;?>" />
</div>
<script type="text/javascript">
var ajaxlock = false;
var ajaxhandle;
function synclogin(){
	if (!ajaxlock) {
		ajaxlock = true;
		$.post(location.href,{code:'<?php echo $logincode;?>'},function(json){
			//console.log(json);
			if (json.status) {
				console.log(json.status);
				if (json.status==200) {
					var nick,uid,username,sex,avatar;
					if (json.info && json.info.User){
						uid = json.info.User.Uin;
						nick = json.info.User.NickName;
						username = json.info.User.UserName;
						sex = json.info.User.Sex;
						avatar = json.info.User.HeadImgUrl;
						$('#content').html('<h2>用户信息</h2><ul><li><b>Uid:</b>'+uid
								+'</li><li><b>Nick:</b>'+nick
								+'</li><li><b>username:</b>'+username
								+'</li><li><b>sex:</b>'+(sex==1?'男':'女')
								+'</li><li><b>avatar:</b>'+avatar
								+'</li></ul>');
					}
					alert('login success, welcome '+nick);

					clearInterval(ajaxhandle);
				}
			}
			ajaxlock = false;
		},'json');
	}
}
$(function(){
	ajaxhandle = setInterval("synclogin()",5000);
});
</script>
</body>