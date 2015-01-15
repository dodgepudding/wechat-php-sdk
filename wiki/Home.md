# wechat-php-sdk

微信公众平台php开发包,细化各项接口操作,支持链式调用  
项目地址：**https://github.com/dodgepudding/wechat-php-sdk**  
项目wiki：**http://binsee.github.io/wechat-php-sdk**  

----

## 使用详解
使用前需先打开微信帐号的开发模式，详细步骤请查看微信公众平台接口使用说明：  
微信公众平台： http://mp.weixin.qq.com/wiki/
微信企业平台： http://qydev.weixin.qq.com/wiki/

微信支付接入文档：https://mp.weixin.qq.com/cgi-bin/readtemplate?t=business/course2_tmpl

微信多客服：http://dkf.qq.com

## 功能目录

 - [[官方API类库]]
    > wechat.class.php
    > 调用官方API，具有更灵活的消息分类响应方式，支持链式调用操作 ；

 - [[企业号API类库]]
    > qywechat.class.php
    > 微信公众平台企业号PHP-SDK
    > 调用官方API，具有更灵活的消息分类响应方式，支持链式调用操作 ；

 - [[API接口错误码]]
    > errCode.php 或 qyerrCode.php
    > 当调用API接口失败时，可以用此类来换取失败原因的中文说明。

 - [[旧版微信支付V2接口类库]]
    > old_version/wechatpay.class.php
    > 当调用API接口失败时，可以用此类来换取失败原因的中文说明。

 - ~~[[非官方扩展类库]]~~*(停止维护)*
    > old_version/wechatext.class.php
    > 非官方扩展API，模拟人工操作微信平台，此方式不保证长期有效。  

 - ~~[[授权登陆类库]]~~*(停止维护)*
    > old_version/wechatauth.class.php
    > 通过微信二维码登陆微信的API, 能实现第三方网站同步登陆

 - ~~[[内嵌JS]]~~*(已废弃)*
    > old_version/wechat.js
    > 微信内嵌网页功能调用js

 - [[为开发框架进行适配]]
    > 为不同的开发框架进行适配缓存操作(保存access_token、jsapi_ticket)，及输出调试日志。



