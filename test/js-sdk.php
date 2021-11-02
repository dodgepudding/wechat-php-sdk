
<?php
if(isset($_GET['operation'])&&$_GET['operation']=='getJsConfig')
{
	include("wechat/wechat.class.php");
	$options = array(
			'token'=>'tokenaccesskey', //填写你设定的key
			'appid'=>'wxdk1234567890', //填写高级调用功能的app id, 请在微信开发模式后台查询
			'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
	);
	$weObj = new Wechat($options);
	$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$res=$weObj->getJsConfig($url);
	exit;
}

?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
<title>JS-SDK 签名测试</title>
<link rel="stylesheet" href="/weui/weui.min.css"/>
<style>
body, html { height: 100%; }
body { font-family: -apple-system-font, Helvetica Neue, Helvetica, sans-serif; }
ul { list-style: outside none none; }
.page, body { background-color: #f8f8f8; }
.link { color: #1aad19; }
.page__hd { padding: 40px; }
.page__title { font-size: 20px; font-weight: 400; text-align: center; }
.page__desc { color: #888; font-size: 14px; margin-top: 5px; text-align: center; }
.page__logo { text-align: center; }
.weui-footer { margin: 20px 0;}
</style>
</head>
<body>
<div class="container">
	<div class="page msg_warn js_show">
		<div class="weui-msg">
			<div class="weui-msg__icon-area"><i class="weui-icon-success weui-icon_msg"></i></div>
			<div class="weui-msg__text-area">
				<h2 class="weui-msg__title">JS-SDK 签名测试</h2>
				<p class="weui-msg__desc">wechat-php-sdk</p>
			</div>
			<div class="weui-msg__opr-area">
				<p class="weui-btn-area">
					<a href="https://github.com/pkkgu/wechat-php-sdk" class="weui-btn weui-btn_primary">wechat-php-sdk</a>
					<!-- a href="javascript:void(0);" class="weui-btn weui-btn_default">云端school</a -->
				</p>
			</div>
			<div class="weui-msg__extra-area">
				<div class="weui-footer">
					<p class="weui-footer__links">
						<a href="javascript:void(0);" class="weui-footer__link">wechat-php-sdk</a>
						<!-- a href="javascript:void(0);" class="weui-footer__link">云端school</a -->
					</p>
					<p class="weui-footer__text">Copyright &copy; 2008-2017</p>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="weui-toptips weui-toptips_warn js_tooltips">操作提示</div>
<script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script> 
<script src="/weui/weui.min.js"></script>
<script src="?operation=getJsConfig"></script>
<script>
wx.ready(function(){
	wx.onMenuShareTimeline({
		title: 'wechat-php-sdk 分享测试', // 分享标题
		link: window.location.href, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
		imgUrl: 'http://wx.eduhz.com/images/logo.jpg', // 分享图标
		success: function () { 
			// 用户确认分享后执行的回调函数
			alert('success');
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
			alert('cancel');
		}
	
	});
});
wx.error(function(res){
	alert(JSON.stringify(res));
});
</script>
</body>
</html>
