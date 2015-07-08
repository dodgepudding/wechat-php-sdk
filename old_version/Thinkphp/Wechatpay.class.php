<?php
/**
 *	微信公众平台PHP-SDK, 旧版微信支付接口(微信支付V2)
 *  @author  dodge <dodgepudding@gmail.com>
 *  @link https://github.com/dodgepudding/wechat-php-sdk
 *  @version 1.2
 *  参考旧版文档 https://mp.weixin.qq.com/cgi-bin/readtemplate?t=business/course2_tmpl&lang=zh_CN
 *  usage:
 *   $options = array(
 *			'appid'=>'wxdk1234567890', //填写高级调用功能的app id
 *			'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
 *			'partnerid'=>'88888888', //财付通商户身份标识
 *			'partnerkey'=>'', //财付通商户权限密钥Key
 *			'paysignkey'=>'' //商户签名密钥Key
 *		);
 *	 $payObj = new Wechatpay($options);
 *   $package = $payObj->createPackage($out_trade_no,$body,$total_fee,$notify_url,$spbill_create_ip,$fee_type,$bank_type,$input_charset,$time_start,$time_expire,$transport_fee,$product_fee,$goods_tag,$attach);
 *
 */
class Wechatpay
{
	const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
	const AUTH_URL = '/token?grant_type=client_credential&';
	const API_BASE_URL_PREFIX = 'https://api.weixin.qq.com'; //以下API接口URL需要使用此前缀
	const PAY_DELIVERNOTIFY = '/pay/delivernotify?';
	const PAY_ORDERQUERY = '/pay/orderquery?';

	private $appid;
	private $appsecret;
	private $access_token;
	private $user_token;
	private $partnerid;
	private $partnerkey;
	private $paysignkey;

	public $debug =  false;
	public $errCode = 40001;
	public $errMsg = "no access";
	private $_logcallback;

	public function __construct($options)
	{
		$this->appid = isset($options['appid'])?$options['appid']:'';
		$this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
		$this->partnerid = isset($options['partnerid'])?$options['partnerid']:'';
		$this->partnerkey = isset($options['partnerkey'])?$options['partnerkey']:'';
		$this->paysignkey = isset($options['paysignkey'])?$options['paysignkey']:'';
		$this->debug = isset($options['debug'])?$options['debug']:false;
		$this->_logcallback = isset($options['logcallback'])?$options['logcallback']:false;
	}

    private function log($log){
    		if ($this->debug ) {
    			if (function_exists($this->_logcallback)) {
    			if (is_array($log)) $log = print_r($log,true);
    			return call_user_func($this->_logcallback,$log);
    			}elseif (class_exists('Log')) {
    				Log::write('wechat：'.$log, Log::DEBUG);
    			}
    		}
    		return false;
    }

	/**
	 * GET 请求
	 * @param string $url
	 */
	private function http_get($url){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}

	/**
	 * POST 请求
	 * @param string $url
	 * @param array $param
	 * @param boolean $post_file 是否文件上传
	 * @return string content
	 */
	private function http_post($url,$param,$post_file=false){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		if (is_string($param) || $post_file) {
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}

	/**
	 * 获取access_token
	 * @param string $appid 如在类初始化时已提供，则可为空
	 * @param string $appsecret 如在类初始化时已提供，则可为空
	 * @param string $token 手动指定access_token，非必要情况不建议用
	 */
	public function checkAuth($appid='',$appsecret='',$token=''){
		if (!$appid || !$appsecret) {
			$appid = $this->appid;
			$appsecret = $this->appsecret;
		}
		$authname = 'wechat_access_token'.$appid;
		if ($token) { //手动指定token，优先使用
		    $this->access_token=$token;
		    return $this->access_token;
		}
		if ($rs = S($authname))  {
			$this->access_token = $rs;
			return $rs;
		}
		$result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->access_token = $json['access_token'];
			$expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
			S($authname,$this->access_token,$expire);
			return $this->access_token;
		}
		return false;
	}

	/**
	 * 删除验证数据
	 * @param string $appid
	 */
	public function resetAuth($appid=''){
		if (!$appid) $appid = $this->appid;
		$this->access_token = '';
		$authname = 'wechat_access_token'.$appid;
		S($authname,null);
		return true;
	}

	/**
	 * 微信api不支持中文转义的json结构
	 * @param array $arr
	 */
	static function json_encode($arr) {
		$parts = array ();
		$is_list = false;
		//Find out if the given array is a numerical array
		$keys = array_keys ( $arr );
		$max_length = count ( $arr ) - 1;
		if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
			$is_list = true;
			for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
				if ($i != $keys [$i]) { //A key fails at position check.
					$is_list = false; //It is an associative array.
					break;
				}
			}
		}
		foreach ( $arr as $key => $value ) {
			if (is_array ( $value )) { //Custom handling for arrays
				if ($is_list)
					$parts [] = self::json_encode ( $value ); /* :RECURSION: */
				else
					$parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
			} else {
				$str = '';
				if (! $is_list)
					$str = '"' . $key . '":';
				//Custom handling for multiple data types
				if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
					$str .= $value; //Numbers
				elseif ($value === false)
				$str .= 'false'; //The booleans
				elseif ($value === true)
				$str .= 'true';
				else
					$str .= '"' . addslashes ( $value ) . '"'; //All other things
				// :TODO: Is there any more datatype we should be in the lookout for? (Object?)
				$parts [] = $str;
			}
		}
		$json = implode ( ',', $parts );
		if ($is_list)
			return '[' . $json . ']'; //Return numerical JSON
		return '{' . $json . '}'; //Return associative JSON
	}

	/**
	 * 获取签名
	 * @param array $arrdata 签名数组
	 * @param string $method 签名方法
	 * @return boolean|string 签名值
	 */
	public function getSignature($arrdata,$method="sha1") {
		if (!function_exists($method)) return false;
		ksort($arrdata);
		$paramstring = "";
		foreach($arrdata as $key => $value)
		{
			if(strlen($paramstring) == 0)
				$paramstring .= $key . "=" . $value;
			else
				$paramstring .= "&" . $key . "=" . $value;
		}
		$paySign = $method($paramstring);
		return $paySign;
	}

	/**
	 * 生成随机字串
	 * @param number $length 长度，默认为16，最长为32字节
	 * @return string
	 */
	public function generateNonceStr($length=16){
		// 密码字符集，可任意添加你需要的字符
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for($i = 0; $i < $length; $i++)
		{
			$str .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		return $str;
	}

	/**
	 * 生成原生支付url
	 * @param number $productid 商品编号，最长为32字节
	 * @return string
	 */
	public function createNativeUrl($productid){
		    $nativeObj["appid"] = $this->appid;
		    $nativeObj["appkey"] = $this->paysignkey;
		    $nativeObj["productid"] = urlencode($productid);
		    $nativeObj["timestamp"] = time();
		    $nativeObj["noncestr"] = $this->generateNonceStr();
		    $nativeObj["sign"] = $this->getSignature($nativeObj);
		    unset($nativeObj["appkey"]);
		    $bizString = "";
		    foreach($nativeObj as $key => $value)
		    {
			if(strlen($bizString) == 0)
				$bizString .= $key . "=" . $value;
			else
				$bizString .= "&" . $key . "=" . $value;
		    }
		    return "weixin://wxpay/bizpayurl?".$bizString;
		    //weixin://wxpay/bizpayurl?sign=XXXXX&appid=XXXXXX&productid=XXXXXX&timestamp=XXXXXX&noncestr=XXXXXX
	}


	/**
	 * 生成订单package字符串
	 * @param string $out_trade_no 必填，商户系统内部的订单号,32个字符内,确保在商户系统唯一
	 * @param string $body 必填，商品描述,128 字节以下
	 * @param int $total_fee 必填，订单总金额,单位为分
	 * @param string $notify_url 必填，支付完成通知回调接口，255 字节以内
	 * @param string $spbill_create_ip 必填，用户终端IP，IPV4字串，15字节内
	 * @param int $fee_type 必填，现金支付币种，默认1:人民币
	 * @param string $bank_type 必填，银行通道类型,默认WX
	 * @param string $input_charset 必填，传入参数字符编码，默认UTF-8，取值有UTF-8和GBK
	 * @param string $time_start 交易起始时间,订单生成时间,格式yyyyMMddHHmmss
	 * @param string $time_expire 交易结束时间,也是订单失效时间
	 * @param int $transport_fee 物流费用,单位为分
	 * @param int $product_fee 商品费用,单位为分,必须保证 transport_fee + product_fee=total_fee
	 * @param string $goods_tag 商品标记,优惠券时可能用到
	 * @param string $attach 附加数据，notify接口原样返回
	 * @return string
	 */
	public function createPackage($out_trade_no,$body,$total_fee,$notify_url,$spbill_create_ip,$fee_type=1,$bank_type="WX",$input_charset="UTF-8",$time_start="",$time_expire="",$transport_fee="",$product_fee="",$goods_tag="",$attach=""){
			$arrdata = array("bank_type" => $bank_type, "body" => $body, "partner" => $this->partnerid, "out_trade_no" => $out_trade_no, "total_fee" => $total_fee, "fee_type" => $fee_type, "notify_url" => $notify_url, "spbill_create_ip" => $spbill_create_ip, "input_charset" => $input_charset);
			if ($time_start)  $arrdata['time_start'] = $time_start;
			if ($time_expire)  $arrdata['time_expire'] = $time_expire;
			if ($transport_fee)  $arrdata['transport_fee'] = $transport_fee;
			if ($product_fee)  $arrdata['product_fee'] = $product_fee;
			if ($goods_tag)  $arrdata['goods_tag'] = $goods_tag;
			if ($attach)  $arrdata['attach'] = $attach;
			ksort($arrdata);
			$paramstring = "";
			foreach($arrdata as $key => $value)
			{
			if(strlen($paramstring) == 0)
				$paramstring .= $key . "=" . $value;
				else
				$paramstring .= "&" . $key . "=" . $value;
			}
			$stringSignTemp = $paramstring . "&key=" . $this->partnerkey;
			$signValue = strtoupper(md5($stringSignTemp));
			$package = http_build_query($arrdata) . "&sign=" . $signValue;
			return $package;
	}

	/**
	 * 支付签名(paySign)生成方法
	 * @param string $package 订单详情字串
	 * @param string $timeStamp 当前时间戳（需与JS输出的一致）
	 * @param string $nonceStr 随机串（需与JS输出的一致）
	 * @return string 返回签名字串
	 */
	public function getPaySign($package, $timeStamp, $nonceStr){
		$arrdata = array("appid" => $this->appid, "timestamp" => $timeStamp, "noncestr" => $nonceStr, "package" => $package, "appkey" => $this->paysignkey);
		$paySign = $this->getSignature($arrdata);
		return $paySign;
	}

	/**
	 * 回调通知签名验证
	 * @param array $orderxml 返回的orderXml的数组表示，留空则自动从post数据获取
	 * @return boolean
	 */
	public function checkOrderSignature($orderxml=''){
		if (!$orderxml) {
			$postStr = file_get_contents("php://input");
			if (!empty($postStr)) {
				$orderxml = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			} else return false;
		}
		$arrdata = array('appid'=>$orderxml['AppId'],'appkey'=>$this->paysignkey,'timestamp'=>$orderxml['TimeStamp'],'noncestr'=>$orderxml['NonceStr'],'openid'=>$orderxml['OpenId'],'issubscribe'=>$orderxml['IsSubscribe']);
		$paySign = $this->getSignature($arrdata);
		if ($paySign!=$orderxml['AppSignature']) return false;
		return true;
	}

	/**
	 * 发货通知
	 * @param string $openid 用户open_id
	 * @param string $transid 交易单号
	 * @param string $out_trade_no 第三方订单号
	 * @param int $status 0:发货失败；1:已发货
	 * @param string $msg 失败原因
	 * @return boolean|array
	 */
	public function sendPayDeliverNotify($openid,$transid,$out_trade_no,$status=1,$msg='ok'){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$postdata = array(
				"appid"=>$this->appid,
				"appkey"=>$this->paysignkey,
				"openid"=>$openid,
				"transid"=>strval($transid),
				"out_trade_no"=>strval($out_trade_no),
				"deliver_timestamp"=>strval(time()),
				"deliver_status"=>strval($status),
				"deliver_msg"=>$msg,
		);
		$postdata['app_signature'] = $this->getSignature($postdata);
		$postdata['sign_method'] = 'sha1';
		unset($postdata['appkey']);
		$result = $this->http_post(self::API_BASE_URL_PREFIX.self::PAY_DELIVERNOTIFY.'access_token='.$this->access_token,self::json_encode($postdata));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}

	/**
	 * 查询订单信息
	 * @param string $out_trade_no 订单号
	 * @return boolean|array
	 */
	public function getPayOrder($out_trade_no) {
		if (!$this->access_token && !$this->checkAuth()) return false;
		$sign = strtoupper(md5("out_trade_no=$out_trade_no&partner={$this->partnerid}&key={$this->partnerkey}"));
		$postdata = array(
				"appid"=>$this->appid,
				"appkey"=>$this->paysignkey,
				"package"=>"out_trade_no=$out_trade_no&partner={$this->partnerid}&sign=$sign",
				"timestamp"=>strval(time()),
		);
		$postdata['app_signature'] = $this->getSignature($postdata);
		$postdata['sign_method'] = 'sha1';
		unset($postdata['appkey']);
		$result = $this->http_post(self::API_BASE_URL_PREFIX.self::PAY_ORDERQUERY.'access_token='.$this->access_token,self::json_encode($postdata));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'].json_encode($postdata);
				return false;
			}
			return $json["order_info"];
		}
		return false;
	}

	/**
	 * 设置用户授权密钥
	 * @param string $user_token
	 * @return string
	 */
	public function setUserToken($user_token) {
		return $this->user_token = $user_token;
	}

	/**
	 * 获取收货地址JS的签名
	 * @tutorial 参考weixin.js脚本的WeixinJS.editAddress方法调用
	 * @param string $appId
	 * @param string $url
	 * @param int $timeStamp
	 * @param string $nonceStr
	 * @param string $user_token
	 * @return Ambigous <boolean, string>
	 */
	public function getAddrSign($url, $timeStamp, $nonceStr, $user_token=''){
		if (!$user_token) $user_token = $this->user_token;
		if (!$user_token) {
			$this->errMsg = 'no user access token found!';
			return false;
		}
		$url = htmlspecialchars_decode($url);
		$arrdata = array(
				'appid'=>$this->appid,
				'url'=>$url,
				'timestamp'=>strval($timeStamp),
				'noncestr'=>$nonceStr,
				'accesstoken'=>$user_token
		);
		return $this->getSignature($arrdata);
	}
}
