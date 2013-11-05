/**
 * 微信网页端调用JS
 * @author dodge
 * @contact dodgepudding@gmail.com
 * @link http://blog.4wer.com/wechat-timeline-share
 * @version 1.1
 * 
 * 自定义分享使用：
 * WeixinJS.hideOptionMenu() 隐藏右上角按钮
 * WeixinJS.hideToolbar() 隐藏工具栏
 * 自定义分享内容数据格式：
 * var dataForWeixin={
	   appId:"",
	   MsgImg:"消息图片路径",
	   TLImg:"时间线图路径",
	   url:"分享url路径",
	   title:"标题",
	   desc:"描述",
	   fakeid:"",
	   callback:function(){}
	};
 */

WeixinJS = typeof WeixinJS!='undefined' || {};
WeixinJS.hideOptionMenu = function() {
	if (typeof WeixinJSBridge!='undefined')
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		WeixinJSBridge.call('hideOptionMenu');
	});
};
WeixinJS.hideToolbar = function() {
	if (typeof WeixinJSBridge!='undefined')
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		WeixinJSBridge.call('hideToolbar');
	});
};

(function(){
   var onBridgeReady=function(){
   WeixinJSBridge.on('menu:share:appmessage', function(argv){
      WeixinJSBridge.invoke('sendAppMessage',{
         "appid":dataForWeixin.appId,
         "img_url":dataForWeixin.MsgImg,
         "img_width":"120",
         "img_height":"120",
         "link":dataForWeixin.url,
         "desc":dataForWeixin.desc,
         "title":dataForWeixin.title
      }, function(res){(dataForWeixin.callback)();});
   });
   WeixinJSBridge.on('menu:share:timeline', function(argv){
      (dataForWeixin.callback)();
      WeixinJSBridge.invoke('shareTimeline',{
         "img_url":dataForWeixin.TLImg,
         "img_width":"120",
         "img_height":"120",
         "link":dataForWeixin.url,
         "desc":dataForWeixin.desc,
         "title":dataForWeixin.title
      }, function(res){});
   });
   WeixinJSBridge.on('menu:share:weibo', function(argv){
      WeixinJSBridge.invoke('shareWeibo',{
         "content":dataForWeixin.title,
         "url":dataForWeixin.url
      }, function(res){(dataForWeixin.callback)();});
   });
   WeixinJSBridge.on('menu:share:facebook', function(argv){
      (dataForWeixin.callback)();
      WeixinJSBridge.invoke('shareFB',{
         "img_url":dataForWeixin.TLImg,
         "img_width":"120",
         "img_height":"120",
         "link":dataForWeixin.url,
         "desc":dataForWeixin.desc,
         "title":dataForWeixin.title
      }, function(res){(dataForWeixin.callback)();});
   });
};
if(document.addEventListener){
   document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
}else if(document.attachEvent){
   document.attachEvent('WeixinJSBridgeReady'   , onBridgeReady);
   document.attachEvent('onWeixinJSBridgeReady' , onBridgeReady);
}
})();