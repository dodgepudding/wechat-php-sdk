# wechatpay.class.php 

旧版微信支付类库(微信支付V2)，已移动至old_version目录下。  
自2014年8月开始申请到的微信支付都是V3接口，据官方说V2的会陆续升级为V3接口，但时间及升级渠道未确认。

### 主要功能 
- 获取access_token **（初级权限）**
- 调用地址组件 **（支付权限）**
- 生成订单签名数据 **（支付权限）**
- 订单成功回调 **（支付权限）**
- 发货通知 **（支付权限）**
- 支付订单查询 **（支付权限）**  
> 备注：  
> 初级权限：基本权限，任何正常的公众号都有此权限  
> 菜单权限：正常的服务号、认证后的订阅号拥有此权限  
> 认证权限：分为订阅号、服务号认证，如前缀服务号则仅认证的服务号有此权限，否则为认证后的订阅号、服务号都有此权限  
> 支付权限：仅认证后的服务号可以申请此权限  


### 初始化动作 
```php
 $options = array(
	'appid'=>'wxdk1234567890', //填写高级调用功能的app id, 请在微信开发模式后台查询
	'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
	'partnerid'=>'88888888', //财付通商户身份标识，支付权限专用，没有可不填
	'partnerkey'=>'', //财付通商户权限密钥Key，支付权限专用
	'paysignkey'=>'' //商户签名密钥Key，支付权限专用
	);
 $weObj = new Wechat($options); //创建实例对象
 //TODO：调用$weObj各实例方法
```

### 主动接口方法:   
 *  checkAuth($appid='',$appsecret='',$token='') 获取access_token。可根据appid和appsecret获取，或手动指定access_token
 *  resetAuth($appid='') 删除验证数据
 *  getSignature($arrdata,'sha1') 生成签名字串  
 *  generateNonceStr($length=16) 获取随机字串  
 *  createNativeUrl($productid) 生成原生支付url
 *  createPackage($out_trade_no,$body,$total_fee,$notify_url,$spbill_create_ip,$fee_type=1,$bank_type="WX",$input_charset="UTF-8",$time_start="",$time_expire="",$transport_fee="",$product_fee="",$goods_tag="",$attach="") 生成订单package字符串  
 *  getPaySign($package, $timeStamp, $nonceStr) 支付签名(paySign)生成方法  
 *  checkOrderSignature($orderxml='') 回调通知签名验证  
 *  sendPayDeliverNotify($openid,$transid,$out_trade_no,$status=1,$msg='ok') 发货通知  
 *  getPayOrder($out_trade_no) 查询订单信息  
 *  setUserToken($user_token) 设置用户授权密钥
 *  getAddrSign($url, $timeStamp, $nonceStr, $user_token='') 获取收货地址JS的签名