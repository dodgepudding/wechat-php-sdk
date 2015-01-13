/**
 * 微信网页端调用JS(官方于微信6.0.2版本发布新版JSAPI接口，此接口文件废弃)
 * @author dodge
 * @contact dodgepudding@gmail.com
 * @link http://blog.4wer.com/wechat-timeline-share
 * @version 1.1
 * 
 * 自定义分享使用：
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
 * 自定义分享内容数据格式：
 * var dataForWeixin={
	   appId:"",
	   MsgImg:"消息图片路径",
	   TLImg:"时间线图路径",
	   url:"分享url路径",
	   title:"标题",
	   desc:"描述",
	   fakeid:"",
	   prepare:function(argv){
	   if (typeof argv.shareTo!='undefined') 
	   	switch(argv.shareTo) {
	   		case 'friend':
	   		//发送给朋友
	   		alert(argv.scene); //friend
	   		break;
	   		case 'timeline':
	   		//发送给朋友
	   		break;
	   		case 'weibo':
	   		//发送到微博
	   		alert(argv.url);
	   		break;
	   		case 'favorite':
	   		//收藏
	   		alert(argv.scene);//favorite
	   		break;
	   		case 'connector':
	   		//分享到第三方应用
	   		alert(argv.scene);//connector
	   		break;
	   		default：
	   	}
	   },
	   callback:function(res){
	   	//发送给好友或应用
	   	if (res.err_msg=='send_app_msg:confirm') {
	   		//todo:func1();
	   		alert(res.err_desc);
	   	}
	   	if (res.err_msg=='send_app_msg:cancel') {
	   		//todo:func2();
	   		alert(res.err_desc);
	   	}
	   	//分享到朋友圈
	   	if (res.err_msg=='share_timeline:ok') {
	   		//todo:func1();
	   		alert(res.err_desc);
	   	}
	   	if (res.err_msg=='share_timeline:cancel') {
	   		//todo:func1();
	   		alert(res.err_desc);
	   	}
	   	//分享到微博
	   	if (res.err_msg=='share_weibo:confirm') {
	   		//todo:func1();
	   		alert(res.err_desc);
	   	}
	   	if (res.err_msg=='share_weibo:cancel') {
	   		//todo:func1();
	   		alert(res.err_desc);
	   	}
	   	//收藏或分享到应用
	   	if (res.err_msg=='send_app_msg:ok') {
	   		//todo:func1();
	   		alert(res.err_desc);
	   	}   	
	   }
	};
 */

WeixinJS = typeof WeixinJS!='undefined' || {};
//隐藏右上角按钮
WeixinJS.hideOptionMenu = function() {
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		if (typeof WeixinJSBridge!='undefined')	WeixinJSBridge.call('hideOptionMenu');
	});
};
//显示右上角按钮
WeixinJS.showOptionMenu = function() {
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		if (typeof WeixinJSBridge!='undefined')	WeixinJSBridge.call('showOptionMenu');
	});
};
//隐藏底部导航栏
WeixinJS.hideToolbar = function() {
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.call('hideToolbar');
	});
};
//显示底部导航栏
WeixinJS.showToolbar = function() {
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.call('showToolbar');
	});
};
//网页获取用户网络状态
netType={"network_type:wifi":"wifi网络","network_type:edge":"非wifi,包含3G/2G","network_type:fail":"网络断开连接","network_type:wwan":"2g或者3g"};
WeixinJS.getNetworkType = function(callback) {
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.invoke('getNetworkType',{},
		function(res){
			//result: network_type:wifi,network_type:edge,network_type:fail,network_type:wwan
			//netType[e.err_msg]
			callback(res.err_msg);
	    });
	});
};
//关闭窗口
WeixinJS.closeWindow = function() {
	if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.invoke("closeWindow", {});
};
//扫描二维码
WeixinJS.scanQRCode = function() {
	if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.invoke("scanQRCode", {});
};
//使用浏览器打开网址
WeixinJS.openUrlByExtBrowser=function(url){
	if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.invoke("openUrlByExtBrowser",{"url" : url});
};
//跳转到指定公众账号页面
WeixinJS.jumpToBizProfile=function(username){
	if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.invoke("jumpToBizProfile",{"tousername" : username});
};
//发送邮件
WeixinJS.sendEmail=function(title,content){
	if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.invoke("sendEmail",{
	    "title" : title,
	    "content" : content
	});
};
//查看地图
WeixinJS.openProductView=function(latitude,longitude,name,address,scale,infoUrl){
	if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.invoke("openProductView",{
	    "latitude" : latitude, //纬度
	    "longitude" : longitude, //经度
	    "name" : name, //名称
	    "address" : address, //地址
	    "scale" : scale, //地图缩放级别
	    "infoUrl" : infoUrl, //查看位置界面底部的超链接            
	});
};
//添加微信账号
WeixinJS.addContact=function weixinAddContact(username){
    if (typeof WeixinJSBridge!='undefined') WeixinJSBridge.invoke("addContact", {
    	"webtype": "1",
    	"username": username
    }, function(e) {
	    WeixinJSBridge.log(e.err_msg);
	    //e.err_msg:add_contact:added 已经添加
	    //e.err_msg:add_contact:cancel 取消添加
	    //e.err_msg:add_contact:ok 添加成功
	    if(e.err_msg == 'add_contact:added' || e.err_msg == 'add_contact:ok'){
	            //关注成功，或者已经关注过
	    }
    });
};

/**
 * 调出微信内图片预览scrollview
 * @param array urls 图片url数组
 * @param string current 当前图片url
 */
WeixinJS.imagePreview = function(urls,current) {
	if (typeof WeixinJSBridge!='undefined') 
		WeixinJSBridge.invoke("imagePreview", {
			current: current,
			urls: urls
		});
};

//微信JsApi支付接口
WeixinJS.payCallback = function(appId,package,timeStamp,nonceStr,signType,paySign,callback){
	if (typeof WeixinJSBridge!='undefined')
	WeixinJSBridge.invoke('getBrandWCPayRequest',{
        "appId" : appId.toString(),
        "timeStamp" : timeStamp.toString(),
        "nonceStr" : nonceStr.toString(),
        "package" : package.toString(),
        "signType" : signType.toString(),
        "paySign" : paySign.toString()
        
    },function(res){
    	// res.err_msg == "get_brand_wcpay_request:ok" return true;
    	// res.err_msg == "get_brand_wcpay_request:cancel" return false;
    	callback(res);
    });
};
//编辑收货地址
WeixinJS.editAddress = function(appId,addrSign,timeStamp,nonceStr,callback){
	var postdata = {
			"appId" : appId.toString(),
            "scope" : "jsapi_address",
            "signType" : "sha1",
            "addrSign" : addrSign.toString(),
            "timeStamp" : timeStamp.toString(),
            "nonceStr" : nonceStr.toString()
	};
	if (typeof WeixinJSBridge!='undefined')
	WeixinJSBridge.invoke('editAddress',postdata, function(res){
	//return res.proviceFirstStageName,res.addressCitySecondStageName,res.addressCountiesThirdStageName,res.addressDetailInfo,res.userName,res.addressPostalCode,res.telNumber
	//error return res.err_msg
	callback(res);
	});
};

(function(){
   if (typeof dataForWeixin=="undefined") return;
   var onBridgeReady=function(){
   WeixinJSBridge.on('menu:share:appmessage', function(argv){
	  (dataForWeixin.prepare)(argv);
      WeixinJSBridge.invoke('sendAppMessage',{
         "appid":dataForWeixin.appId,
         "img_url":dataForWeixin.MsgImg,
         "img_width":"120",
         "img_height":"120",
         "link":dataForWeixin.url,
         "desc":dataForWeixin.desc,
         "title":dataForWeixin.title
      }, function(res){(dataForWeixin.callback)(res);});
   });
   WeixinJSBridge.on('menu:share:timeline', function(argv){
	  (dataForWeixin.prepare)(argv);
      WeixinJSBridge.invoke('shareTimeline',{
         "img_url":dataForWeixin.TLImg,
         "img_width":"120",
         "img_height":"120",
         "link":dataForWeixin.url,
         "desc":dataForWeixin.desc,
         "title":dataForWeixin.title
      }, function(res){(dataForWeixin.callback)(res);});
   });
   WeixinJSBridge.on('menu:share:weibo', function(argv){
	  (dataForWeixin.prepare)(argv);
      WeixinJSBridge.invoke('shareWeibo',{
         "content":dataForWeixin.title,
         "url":dataForWeixin.url
      }, function(res){(dataForWeixin.callback)(res);});
   });
   WeixinJSBridge.on('menu:share:facebook', function(argv){
	  (dataForWeixin.prepare)(argv);
      WeixinJSBridge.invoke('shareFB',{
         "img_url":dataForWeixin.TLImg,
         "img_width":"120",
         "img_height":"120",
         "link":dataForWeixin.url,
         "desc":dataForWeixin.desc,
         "title":dataForWeixin.title
      }, function(res){(dataForWeixin.callback)(res);});
   });
};
if(document.addEventListener){
   document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
}else if(document.attachEvent){
   document.attachEvent('WeixinJSBridgeReady'   , onBridgeReady);
   document.attachEvent('onWeixinJSBridgeReady' , onBridgeReady);
}
})();
