#wechat.js

**此JS脚本已经废弃不再更新，原因是官方在微信6.0.2版本开放了全新的JSAPI接口，更全面好用。请查看：[微信公众平台WIKI](http://mp.weixin.qq.com/wiki)**

##微信内嵌网页特殊功能js调用：
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
可以参考weshare.html及wechat.js的备注进行使用