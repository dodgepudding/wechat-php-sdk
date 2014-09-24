<?php
/**
 *	微信公众平台PHP-SDK, 官方API部分
 *  @author  dodge <dodgepudding@gmail.com>
 *  @link https://github.com/dodgepudding/wechat-php-sdk
 *  @version 1.2
 *  usage:
 *   $options = array(
 *			'token'=>'tokenaccesskey', //填写你设定的key
 *			'appid'=>'wxdk1234567890', //填写高级调用功能的app id
 *			'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
 *			'partnerid'=>'88888888', //财付通商户身份标识
 *			'partnerkey'=>'', //财付通商户权限密钥Key
 *			'paysignkey'=>'' //商户签名密钥Key
 *		);
 *	 $weObj = new Wechat($options);
 *   $weObj->valid();
 *   $type = $weObj->getRev()->getRevType();
 *   switch($type) {
 *   		case Wechat::MSGTYPE_TEXT:
 *   			$weObj->text("hello, I'm wechat")->reply();
 *   			exit;
 *   			break;
 *   		case Wechat::MSGTYPE_EVENT:
 *   			....
 *   			break;
 *   		case Wechat::MSGTYPE_IMAGE:
 *   			...
 *   			break;
 *   		default:
 *   			$weObj->text("help info")->reply();
 *   }
 *   
 *   //获取菜单操作:
 *   $menu = $weObj->getMenu();
 *   //设置菜单
 *   $newmenu =  array(
 *   		"button"=>
 *   			array(
 *   				array('type'=>'click','name'=>'最新消息','key'=>'MENU_KEY_NEWS'),
 *   				array('type'=>'view','name'=>'我要搜索','url'=>'http://www.baidu.com'),
 *   				)
 *  		);
 *   $result = $weObj->createMenu($newmenu);
 */
class Wechat
{
	const MSGTYPE_TEXT = 'text';
	const MSGTYPE_IMAGE = 'image';
	const MSGTYPE_LOCATION = 'location';
	const MSGTYPE_LINK = 'link';
	const MSGTYPE_EVENT = 'event';
	const MSGTYPE_MUSIC = 'music';
	const MSGTYPE_NEWS = 'news';
	const MSGTYPE_VOICE = 'voice';
	const MSGTYPE_VIDEO = 'video';
	const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
	const AUTH_URL = '/token?grant_type=client_credential&';
	const MENU_CREATE_URL = '/menu/create?';
	const MENU_GET_URL = '/menu/get?';
	const MENU_DELETE_URL = '/menu/delete?';
	const MEDIA_GET_URL = '/media/get?';
	const QRCODE_CREATE_URL='/qrcode/create?';
	const QR_SCENE = 0;
	const QR_LIMIT_SCENE = 1;
	const QRCODE_IMG_URL='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';
	const SHORT_URL='/shorturl?';
	const USER_GET_URL='/user/get?';
	const USER_INFO_URL='/user/info?';
	const USER_UPDATEREMARK_URL='/user/info/updateremark?';	
	const GROUP_GET_URL='/groups/get?';
	const USER_GROUP_URL='/groups/getid?';
	const GROUP_CREATE_URL='/groups/create?';
	const GROUP_UPDATE_URL='/groups/update?';
	const GROUP_MEMBER_UPDATE_URL='/groups/members/update?';
	const CUSTOM_SEND_URL='/message/custom/send?';
	const MEDIA_UPLOADNEWS_URL = '/media/uploadnews?';
	const MASS_SEND_URL = '/message/mass/send?';
	const TEMPLATE_SEND_URL = '/message/template/send?';
	const MASS_SEND_GROUP_URL = '/message/mass/sendall?';
	const MASS_DELETE_URL = '/message/mass/delete?';
	const UPLOAD_MEDIA_URL = 'http://file.api.weixin.qq.com/cgi-bin';
	const MEDIA_UPLOAD = '/media/upload?';
	const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
	const OAUTH_AUTHORIZE_URL = '/authorize?';
	const OAUTH_TOKEN_PREFIX = 'https://api.weixin.qq.com/sns/oauth2';
	const OAUTH_TOKEN_URL = '/access_token?';
	const OAUTH_REFRESH_URL = '/refresh_token?';
	const OAUTH_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo?';
	const OAUTH_AUTH_URL = 'https://api.weixin.qq.com/sns/auth?';
	const PAY_DELIVERNOTIFY = 'https://api.weixin.qq.com/pay/delivernotify?';
	const PAY_ORDERQUERY = 'https://api.weixin.qq.com/pay/orderquery?';
	const CUSTOM_SERVICE_GET_RECORD = '/customservice/getrecord?';
	const CUSTOM_SERVICE_GET_KFLIST = '/customservice/getkflist?';
	const CUSTOM_SERVICE_GET_ONLINEKFLIST = '/customservice/getkflist?';
	
	private $token;
	private $appid;
	private $appsecret;
	private $access_token;
	private $user_token;
	private $partnerid;
	private $partnerkey;
	private $paysignkey;
	private $_msg;
	private $_funcflag = false;
	private $_receive;
	private $_text_filter = true;
	public $debug =  false;
	public $errCode = 40001;
	public $errMsg = "no access";
	private $_logcallback;
	
	public function __construct($options)
	{
		$this->token = isset($options['token'])?$options['token']:'';
		$this->appid = isset($options['appid'])?$options['appid']:'';
		$this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
		$this->partnerid = isset($options['partnerid'])?$options['partnerid']:'';
		$this->partnerkey = isset($options['partnerkey'])?$options['partnerkey']:'';
		$this->paysignkey = isset($options['paysignkey'])?$options['paysignkey']:'';
		$this->debug = isset($options['debug'])?$options['debug']:false;
		$this->_logcallback = isset($options['logcallback'])?$options['logcallback']:false;
	}
	
	/**
	 * For weixin server validation 
	 */	
	private function checkSignature()
	{
        $signature = isset($_GET["signature"])?$_GET["signature"]:'';
        $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';
        		
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * For weixin server validation 
	 * @param bool $return 是否返回
	 */
	public function valid($return=false)
    {
        $echoStr = isset($_GET["echostr"]) ? $_GET["echostr"]: '';
        if ($return) {
        		if ($echoStr) {
        			if ($this->checkSignature()) 
        				return $echoStr;
        			else
        				return false;
        		} else 
        			return $this->checkSignature();
        } else {
	        	if ($echoStr) {
	        		if ($this->checkSignature())
	        			die($echoStr);
	        		else 
	        			die('no access');
	        	}  else {
	        		if ($this->checkSignature())
	        			return true;
	        		else
	        			die('no access');
	        	}
        }
        return false;
    }
    
	/**
	 * 设置发送消息
	 * @param array $msg 消息数组
	 * @param bool $append 是否在原消息数组追加
	 */
    public function Message($msg = '',$append = false){
    		if (is_null($msg)) {
    			$this->_msg =array();
    		}elseif (is_array($msg)) {
    			if ($append)
    				$this->_msg = array_merge($this->_msg,$msg);
    			else
    				$this->_msg = $msg;
    			return $this->_msg;
    		} else {
    			return $this->_msg;
    		}
    }
    
    public function setFuncFlag($flag) {
    		$this->_funcflag = $flag;
    		return $this;
    }
    
    private function log($log){
    		if ($this->debug && function_exists($this->_logcallback)) {
    			if (is_array($log)) $log = print_r($log,true);
    			return call_user_func($this->_logcallback,$log);
    		}
    }
    
    /**
     * 获取微信服务器发来的信息
     */
	public function getRev()
	{
		if ($this->_receive) return $this;
		$postStr = file_get_contents("php://input");
		$this->log($postStr);
		if (!empty($postStr)) {
			$this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		}
		return $this;
	}
	
	/**
	 * 获取微信服务器发来的信息
	 */
	public function getRevData()
	{
		return $this->_receive;
	}
	
	/**
	 * 获取消息发送者
	 */
	public function getRevFrom() {
		if (isset($this->_receive['FromUserName']))
			return $this->_receive['FromUserName'];
		else 
			return false;
	}
	
	/**
	 * 获取消息接受者
	 */
	public function getRevTo() {
		if (isset($this->_receive['ToUserName']))
			return $this->_receive['ToUserName'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息的类型
	 */
	public function getRevType() {
		if (isset($this->_receive['MsgType']))
			return $this->_receive['MsgType'];
		else 
			return false;
	}
	
	/**
	 * 获取消息ID
	 */
	public function getRevID() {
		if (isset($this->_receive['MsgId']))
			return $this->_receive['MsgId'];
		else 
			return false;
	}
	
	/**
	 * 获取消息发送时间
	 */
	public function getRevCtime() {
		if (isset($this->_receive['CreateTime']))
			return $this->_receive['CreateTime'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息内容正文
	 */
	public function getRevContent(){
		if (isset($this->_receive['Content']))
			return $this->_receive['Content'];
		else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容，需申请开通
			return $this->_receive['Recognition'];
		else
			return false;
	}
	
	/**
	 * 获取接收消息图片
	 */
	public function getRevPic(){
		if (isset($this->_receive['PicUrl']))
			return array(
				'mediaid'=>$this->_receive['MediaId'],
				'picurl'=>(string)$this->_receive['PicUrl'],    //防止picurl为空导致解析出错
			);
		else 
			return false;
	}
	
	/**
	 * 获取接收消息链接
	 */
	public function getRevLink(){
		if (isset($this->_receive['Url'])){
			return array(
				'url'=>$this->_receive['Url'],
				'title'=>$this->_receive['Title'],
				'description'=>$this->_receive['Description']
			);
		} else 
			return false;
	}
	
	/**
	 * 获取接收地理位置
	 */
	public function getRevGeo(){
		if (isset($this->_receive['Location_X'])){
			return array(
				'x'=>$this->_receive['Location_X'],
				'y'=>$this->_receive['Location_Y'],
				'scale'=>$this->_receive['Scale'],
				'label'=>$this->_receive['Label']
			);
		} else 
			return false;
	}
	
	/**
	 * 获取上报地理位置事件
	 */
	public function getRevEventGeo(){
        	if (isset($this->_receive['Latitude'])){
        		 return array(
				'x'=>$this->_receive['Latitude'],
				'y'=>$this->_receive['Longitude'],
				'precision'=>$this->_receive['Precision'],
			);
		} else
			return false;
	}
	
	/**
	 * 获取接收事件推送
	 */
	public function getRevEvent(){
		if (isset($this->_receive['Event'])){
			$array['event'] = $this->_receive['Event'];
		}
		if (isset($this->_receive['EventKey'])){
			$array['key'] = $this->_receive['EventKey'];
		}
		if (isset($array) && count($array) > 0) {
			return $array;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取自定义菜单的扫码推事件信息
	 * 
	 * 事件类型为以下两种时则调用此方法有效
	 * Event	 事件类型，scancode_push
	 * Event	 事件类型，scancode_waitmsg
	 * 
	 * @return: array | false
	 * array (
	 *     'ScanType'=>'qrcode',
	 *     'ScanResult'=>'123123'
	 * )
	 */
	public function getRevScanInfo(){
		if (isset($this->_receive['ScanCodeInfo'])){
		    if (!is_array($this->_receive['SendPicsInfo'])) {
		        $array=(array)$this->_receive['ScanCodeInfo'];
		        $this->_receive['ScanCodeInfo']=$array;
		    }else {
		        $array=$this->_receive['ScanCodeInfo'];
		    }
		}
		if (isset($array) && count($array) > 0) {
			return $array;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取自定义菜单的图片发送事件信息
	 * 
	 * 事件类型为以下三种时则调用此方法有效
	 * Event	 事件类型，pic_sysphoto        弹出系统拍照发图的事件推送
	 * Event	 事件类型，pic_photo_or_album  弹出拍照或者相册发图的事件推送
	 * Event	 事件类型，pic_weixin          弹出微信相册发图器的事件推送
	 * 
	 * @return: array | false
	 * array (
	 *   'Count' => '2',
	 *   'PicList' => 
	 *   array (
	 *     'item' => 
	 *     array (
	 *       0 => 
	 *       array (
	 *         'PicMd5Sum' => 'aaae42617cf2a14342d96005af53624c',
	 *       ),
	 *       1 => 
	 *       array (
	 *         'PicMd5Sum' => '149bd39e296860a2adc2f1bb81616ff8',
	 *       ),
	 *     ),
	 *   ),
	 * )
	 * 
	 */
	public function getRevSendPicsInfo(){
		if (isset($this->_receive['SendPicsInfo'])){
		    if (!is_array($this->_receive['SendPicsInfo'])) {
		        $array=(array)$this->_receive['SendPicsInfo'];
		        if (isset($array['PicList'])){
		            $array['PicList']=(array)$array['PicList'];
		            $item=$array['PicList']['item'];
		            $array['PicList']['item']=array();
		            foreach ( $item as $key => $value ){
		                $array['PicList']['item'][$key]=(array)$value;
		            }
		        }
		        $this->_receive['SendPicsInfo']=$array;
		    } else {
		        $array=$this->_receive['SendPicsInfo'];
		    }
		}
		if (isset($array) && count($array) > 0) {
			return $array;
		} else {
			return false;
		}
	}

	/**
	 * 获取自定义菜单的地理位置选择器事件推送
	 *
	 * 事件类型为以下时则可以调用此方法有效
	 * Event	 事件类型，location_select        弹出系统拍照发图的事件推送
	 *
	 * @return: array | false
	 * array (
	 *   'Location_X' => '33.731655000061',
	 *   'Location_Y' => '113.29955200008047',
	 *   'Scale' => '16',
	 *   'Label' => '某某市某某区某某路',
	 *   'Poiname' => '',
	 * )
	 * 
	 */
	public function getRevSendGeoInfo(){
	    if (isset($this->_receive['SendLocationInfo'])){
	        if (!is_array($this->_receive['SendLocationInfo'])) {
	            $array=(array)$this->_receive['SendLocationInfo'];
	            if (empty($array['Poiname'])) {
	                $array['Poiname']="";
	            }
	            if (empty($array['Label'])) {
	                $array['Label']="";
	            }
	            $this->_receive['SendLocationInfo']=$array;
	        } else {
	            $array=$this->_receive['SendLocationInfo'];
	        }
	    }
	    if (isset($array) && count($array) > 0) {
	        return $array;
	    } else {
	        return false;
	    }
	}
	
	/**
	 * 获取接收语音推送
	 */
	public function getRevVoice(){
		if (isset($this->_receive['MediaId'])){
			return array(
				'mediaid'=>$this->_receive['MediaId'],
				'format'=>$this->_receive['Format'],
			);
		} else 
			return false;
	}
	
	/**
	 * 获取接收视频推送
	 */
	public function getRevVideo(){
		if (isset($this->_receive['MediaId'])){
			return array(
					'mediaid'=>$this->_receive['MediaId'],
					'thumbmediaid'=>$this->_receive['ThumbMediaId']
			);
		} else
			return false;
	}
	
	/**
	 * 获取接收TICKET
	 */
	public function getRevTicket(){
		if (isset($this->_receive['Ticket'])){
			return $this->_receive['Ticket'];
		} else
			return false;
	}
	
	/**
	* 获取二维码的场景值
	*/
	public function getRevSceneId (){
		if (isset($this->_receive['EventKey'])){
			return str_replace('qrscene_','',$this->_receive['EventKey']);
		} else{
			return false;
		}
	}
	
	/**
	* 获取模板消息ID
	* 经过验证，这个和普通的消息MsgId不一样
	*/
	public function getRevTplMsgID(){
		if (isset($this->_receive['MsgID'])){
			return $this->_receive['MsgID'];
		} else 
			return false;
	}
	
	/**
	* 获取模板消息发送状态
	*/
	public function getRevStatus(){
		if (isset($this->_receive['Status'])){
			return $this->_receive['Status'];
		} else 
			return false;
	}
	
	public static function xmlSafeStr($str)
	{   
		return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';   
	} 
	
	/**
	 * 数据XML编码
	 * @param mixed $data 数据
	 * @return string
	 */
	public static function data_to_xml($data) {
	    $xml = '';
	    foreach ($data as $key => $val) {
	        is_numeric($key) && $key = "item id=\"$key\"";
	        $xml    .=  "<$key>";
	        $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
	        list($key, ) = explode(' ', $key);
	        $xml    .=  "</$key>";
	    }
	    return $xml;
	}	
	
	/**
	 * XML编码
	 * @param mixed $data 数据
	 * @param string $root 根节点名
	 * @param string $item 数字索引的子节点名
	 * @param string $attr 根节点属性
	 * @param string $id   数字索引子节点key转换的属性名
	 * @param string $encoding 数据编码
	 * @return string
	*/
	public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
	    if(is_array($attr)){
	        $_attr = array();
	        foreach ($attr as $key => $value) {
	            $_attr[] = "{$key}=\"{$value}\"";
	        }
	        $attr = implode(' ', $_attr);
	    }
	    $attr   = trim($attr);
	    $attr   = empty($attr) ? '' : " {$attr}";
	    $xml   = "<{$root}{$attr}>";
	    $xml   .= self::data_to_xml($data, $item, $id);
	    $xml   .= "</{$root}>";
	    return $xml;
	}
	
	/**
	 * 过滤文字回复\r\n换行符
	 * @param string $text
	 * @return string|mixed
	 */
	private function _auto_text_filter($text) {
		if (!$this->_text_filter) return $text;
		return str_replace("\r\n", "\n", $text);
	}
	
	/**
	 * 设置回复消息
	 * Examle: $obj->text('hello')->reply();
	 * @param string $text
	 */
	public function text($text='')
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'MsgType'=>self::MSGTYPE_TEXT,
			'Content'=>$this->_auto_text_filter($text),
			'CreateTime'=>time(),
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
	/**
	 * 设置回复消息
	 * Examle: $obj->image('media_id')->reply();
	 * @param string $mediaid
	 */
	public function image($mediaid='')
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'MsgType'=>self::MSGTYPE_IMAGE,
			'Image'=>array('MediaId'=>$mediaid),
			'CreateTime'=>time(),
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
	
	/**
	 * 设置回复消息
	 * Examle: $obj->voice('media_id')->reply();
	 * @param string $mediaid
	 */
	public function voice($mediaid='')
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'MsgType'=>self::MSGTYPE_IMAGE,
			'Voice'=>array('MediaId'=>$mediaid),
			'CreateTime'=>time(),
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
	
	/**
	 * 设置回复消息
	 * Examle: $obj->video('media_id','title','description')->reply();
	 * @param string $mediaid
	 */
	public function video($mediaid='',$title,$description)
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'MsgType'=>self::MSGTYPE_IMAGE,
			'Video'=>array(
			        'MediaId'=>$mediaid,
			        'Title'=>$mediaid,
			        'Description'=>$mediaid,
			),
			'CreateTime'=>time(),
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
	
	/**
	 * 设置回复音乐
	 * @param string $title
	 * @param string $desc
	 * @param string $musicurl
	 * @param string $hgmusicurl
	 */
	public function music($title,$desc,$musicurl,$hgmusicurl='') {
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_MUSIC,
			'Music'=>array(
				'Title'=>$title,
				'Description'=>$desc,
				'MusicUrl'=>$musicurl,
				'HQMusicUrl'=>$hgmusicurl
			),
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
	
	/**
	 * 设置回复图文
	 * @param array $newsData 
	 * 数组结构:
	 *  array(
	 *  	"0"=>array(
	 *  		'Title'=>'msg title',
	 *  		'Description'=>'summary text',
	 *  		'PicUrl'=>'http://www.domain.com/1.jpg',
	 *  		'Url'=>'http://www.domain.com/1.html'
	 *  	),
	 *  	"1"=>....
	 *  )
	 */
	public function news($newsData=array())
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$count = count($newsData);
		
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'MsgType'=>self::MSGTYPE_NEWS,
			'CreateTime'=>time(),
			'ArticleCount'=>$count,
			'Articles'=>$newsData,
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
	
	/**
	 * 
	 * 回复微信服务器, 此函数支持链式操作
	 * Example: $this->text('msg tips')->reply();
	 * @param string $msg 要发送的信息, 默认取$this->_msg
	 * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
	 */
	public function reply($msg=array(),$return = false)
	{
		if (empty($msg)) 
			$msg = $this->_msg;
		$xmldata=  $this->xml_encode($msg);
		$this->log($xmldata);
		if ($return)
			return $xmldata;
		else
			echo $xmldata;
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
	 * @return string content
	 */
	private function http_post($url,$param){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		}
		if (is_string($param)) {
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
	 * 通用auth验证方法，暂时仅用于菜单更新操作
	 * @param string $appid
	 * @param string $appsecret
	 * @param string $token 手动指定access_token，非必要情况不建议用
	 */
	public function checkAuth($appid='',$appsecret='',$token=''){
		if (!$appid || !$appsecret) {
			$appid = $this->appid;
			$appsecret = $this->appsecret;
		}
		if ($token) { //手动指定token，优先使用
		    $this->access_token=$token;
		    return $this->access_token;
		}
		//TODO: get the cache access_token
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
			//TODO: cache access_token
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
		//TODO: remove cache
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
				if (is_numeric ( $value ) && $value<2000000000)
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
	 * 创建菜单
	 * @param array $data 菜单数组数据
	 * example:
     * 	array (
     * 	    'button' => array (
     * 	      0 => array (
     * 	        'name' => '扫码',
     * 	        'sub_button' => array (
     * 	            0 => array (
     * 	              'type' => 'scancode_waitmsg',
     * 	              'name' => '扫码带提示',
     * 	              'key' => 'rselfmenu_0_0',
     * 	              'sub_button' => ''
     * 	            ),
     * 	            1 => array (
     * 	              'type' => 'scancode_push',
     * 	              'name' => '扫码推事件',
     * 	              'key' => 'rselfmenu_0_1',
     * 	              'sub_button' => ''
     * 	            ),
     * 	        ),
     * 	      ),
     * 	      1 => array (
     * 	        'name' => '发图',
     * 	        'sub_button' => array (
     * 	            0 => array (
     * 	              'type' => 'pic_sysphoto',
     * 	              'name' => '系统拍照发图',
     * 	              'key' => 'rselfmenu_1_0',
     * 	              'sub_button' => ''
     * 	            ),
     * 	            1 => array (
     * 	              'type' => 'pic_photo_or_album',
     * 	              'name' => '拍照或者相册发图',
     * 	              'key' => 'rselfmenu_1_1',
     * 	              'sub_button' => ''
     * 	            )
     * 	        ),
     * 	      ),
     * 	      2 => array (
     * 	        'type' => 'location_select',
     * 	        'name' => '发送位置',
     * 	        'key' => 'rselfmenu_2_0',
     * 	        'sub_button' => ''
     * 	      ),
     * 	    ),
     * 	)
     * type可以选择为以下几种，其中5-8除了收到菜单事件以外，还会单独收到对应类型的信息。
     * 1、click：点击推事件
     * 2、view：跳转URL
     * 3、scancode_push：扫码推事件
     * 4、scancode_waitmsg：扫码推事件且弹出“消息接收中”提示框
     * 5、pic_sysphoto：弹出系统拍照发图
     * 6、pic_photo_or_album：弹出拍照或者相册发图
     * 7、pic_weixin：弹出微信相册发图器
     * 8、location_select：弹出地理位置选择器
	 */
	public function createMenu($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::MENU_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 获取菜单
	 * @return array('menu'=>array(....s))
	 */
	public function getMenu(){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::MENU_GET_URL.'access_token='.$this->access_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	
	/**
	 * 删除菜单
	 * @return boolean
	 */
	public function deleteMenu(){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::MENU_DELETE_URL.'access_token='.$this->access_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * 上传多媒体文件
	 * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
	 * @param array $data {"media":'@Path\filename.jpg'}
	 * @param type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
	 * @return boolean|array
	 */
	public function uploadMedia($data, $type){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::UPLOAD_MEDIA_URL.self::MEDIA_UPLOAD.'access_token='.$this->access_token.'&type='.$type,$data);
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
	 * 根据媒体文件ID获取媒体文件
	 * @param string $media_id 媒体文件id
	 * @return raw data
	 */
	public function getMedia($media_id){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::UPLOAD_MEDIA_URL.self::MEDIA_GET_URL.'access_token='.$this->access_token.'&media_id='.$media_id);
		if ($result)
		{
			$json = json_decode($result,true);
			if (isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}

	/**
	 * 上传图文消息素材
	 * @param array $data 消息结构{"articles":[{...}]}
	 * @return boolean|array
	 */
	public function uploadArticles($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::MEDIA_UPLOADNEWS_URL.'access_token='.$this->access_token,self::json_encode($data));
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
	 * 高级群发消息, 根据OpenID列表群发图文消息
	 * @param array $data 消息结构{ "touser":[ "OPENID1", "OPENID2" ], "mpnews":{ "media_id":"123dsdajkasd231jhksad" }, "msgtype":"mpnews" }
	 * @return boolean|array
	 */
	public function sendMassMessage($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::MASS_SEND_URL.'access_token='.$this->access_token,self::json_encode($data));
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
	 * 高级群发消息, 根据群组id群发图文消息
	 * @param array $data 消息结构{ "filter":[ "group_id": "2" ], "mpnews":{ "media_id":"123dsdajkasd231jhksad" }, "msgtype":"mpnews" }
	 * @return boolean|array
	 */
	public function sendGroupMassMessage($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::MASS_SEND_GROUP_URL.'access_token='.$this->access_token,self::json_encode($data));
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
	 * 高级群发消息, 删除群发图文消息
	 * @param int $msg_id 消息id
	 * @return boolean|array
	 */
	public function deleteMassMessage($msg_id){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::MASS_DELETE_URL.'access_token='.$this->access_token,self::json_encode(array('msg_id'=>$msg_id)));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 创建二维码ticket
	 * @param int $scene_id 自定义追踪id
	 * @param int $type 0:临时二维码；1:永久二维码(此时expire参数无效)
	 * @param int $expire 临时二维码有效期，最大为1800秒
	 * @return array('ticket'=>'qrcode字串','expire_seconds'=>1800,'url'=>'二维码图片解析后的地址')
	 */
	public function getQRCode($scene_id,$type=0,$expire=1800){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$data = array(
			'action_name'=>$type?"QR_LIMIT_SCENE":"QR_SCENE",
			'expire_seconds'=>$expire,
			'action_info'=>array('scene'=>array('scene_id'=>$scene_id))
		);
		if ($type == 1) {
			unset($data['expire_seconds']);
		}
		$result = $this->http_post(self::API_URL_PREFIX.self::QRCODE_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
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
	 * 获取二维码图片
	 * @param string $ticket 传入由getQRCode方法生成的ticket参数
	 * @return string url 返回http地址
	 */
	public function getQRUrl($ticket) {
		return self::QRCODE_IMG_URL.$ticket;
	}
	
	/**
	 * 长链接转短链接接口
	 * @param string $long_url 传入要转换的长url
	 * @return boolean|string url 成功则返回转换后的短url
	 */
	public function getShortUrl($long_url){
	    if (!$this->access_token && !$this->checkAuth()) return false;
	    $data = array(
            'action'=>'long2short',
            'long_url'=>$long_url
	    );
	    $result = $this->http_post(self::API_URL_PREFIX.self::SHORT_URL.'access_token='.$this->access_token,self::json_encode($data));
	    if ($result)
	    {
	        $json = json_decode($result,true);
	        if (!$json || !empty($json['errcode'])) {
	            $this->errCode = $json['errcode'];
	            $this->errMsg = $json['errmsg'];
	            return false;
	        }
	        return $json['short_url'];
	    }
	    return false;
	}

	/**
	 * 批量获取关注用户列表
	 * @param unknown $next_openid
	 */
	public function getUserList($next_openid=''){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::USER_GET_URL.'access_token='.$this->access_token.'&next_openid='.$next_openid);
		if ($result)
		{
			$json = json_decode($result,true);
			if (isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	
	/**
	 * 获取关注者详细信息
	 * @param string $openid
	 * @return array {subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
	 * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
	 */
	public function getUserInfo($openid){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::USER_INFO_URL.'access_token='.$this->access_token.'&openid='.$openid);
		if ($result)
		{
			$json = json_decode($result,true);
			if (isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}

	/**
	 * 设置用户备注名
	 * @param string $openid
	 * @param string $remark 备注名
	 * @return boolean|array
	 */
	public function updateUserRemark($openid,$remark){
	    if (!$this->access_token && !$this->checkAuth()) return false;
	    $data = array(
			'openid'=>$openid,
			'remark'=>$remark
	    );
	    $result = $this->http_post(self::API_URL_PREFIX.self::USER_UPDATEREMARK_URL.'access_token='.$this->access_token,$data);
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
	 * 获取用户分组列表
	 * @return boolean|array
	 */
	public function getGroup(){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::GROUP_GET_URL.'access_token='.$this->access_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	
	/**
	 * 获取用户所在分组
	 * @param string $openid
	 * @return boolean|int 成功则返回用户分组id
	 */
	public function getUserGroup($openid){
	    if (!$this->access_token && !$this->checkAuth()) return false;
	    $data = array(
	            'openid'=>$openid
	    );
	    $result = $this->http_post(self::API_URL_PREFIX.self::USER_GROUP_URL.'access_token='.$this->access_token,self::json_encode($data));
	    if ($result)
	    {
	        $json = json_decode($result,true);
	        if (!$json || !empty($json['errcode'])) {
	            $this->errCode = $json['errcode'];
	            $this->errMsg = $json['errmsg'];
	            return false;
	        } else 
                if (isset($json['groupid'])) return $json['groupid'];
	    }
	    return false;
	}
    
	/**
	 * 新增自定分组
	 * @param string $name 分组名称
	 * @return boolean|array
	 */
	public function createGroup($name){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$data = array(
				'group'=>array('name'=>$name)
		);
		$result = $this->http_post(self::API_URL_PREFIX.self::GROUP_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
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
	 * 更改分组名称
	 * @param int $groupid 分组id
	 * @param string $name 分组名称
	 * @return boolean|array
	 */
	public function updateGroup($groupid,$name){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$data = array(
				'group'=>array('id'=>$groupid,'name'=>$name)
		);
		$result = $this->http_post(self::API_URL_PREFIX.self::GROUP_UPDATE_URL.'access_token='.$this->access_token,self::json_encode($data));
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
	 * 移动用户分组
	 * @param int $groupid 分组id
	 * @param string $openid 用户openid
	 * @return boolean|array
	 */
	public function updateGroupMembers($groupid,$openid){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$data = array(
				'openid'=>$openid,
				'to_groupid'=>$groupid
		);
		$result = $this->http_post(self::API_URL_PREFIX.self::GROUP_MEMBER_UPDATE_URL.'access_token='.$this->access_token,self::json_encode($data));
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
	 * 发送客服消息
	 * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
	 * @return boolean|array
	 */
	public function sendCustomMessage($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::CUSTOM_SEND_URL.'access_token='.$this->access_token,self::json_encode($data));
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
	 * oauth 授权跳转接口
	 * @param string $callback 回调URI
	 * @return string
	 */
	public function getOauthRedirect($callback,$state='',$scope='snsapi_userinfo'){
		return self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
	}
	
	/**
	 * 通过code获取Access Token
	 * @return array {access_token,expires_in,refresh_token,openid,scope}
	 */
	public function getOauthAccessToken(){
		$code = isset($_GET['code'])?$_GET['code']:'';
		if (!$code) return false;
		$result = $this->http_get(self::OAUTH_TOKEN_PREFIX.self::OAUTH_TOKEN_URL.'appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->user_token = $json['access_token'];
			return $json;
		}
		return false;
	}
	
	/**
	 * 刷新access token并续期
	 * @param string $refresh_token
	 * @return boolean|mixed
	 */
	public function getOauthRefreshToken($refresh_token){
		$result = $this->http_get(self::OAUTH_TOKEN_PREFIX.self::OAUTH_REFRESH_URL.'appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->user_token = $json['access_token'];
			return $json;
		}
		return false;
	}
	
	/**
	 * 获取授权后的用户资料
	 * @param string $access_token
	 * @param string $openid
	 * @return array {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
	 * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
	 */
	public function getOauthUserinfo($access_token,$openid){
		$result = $this->http_get(self::OAUTH_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);
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
	 * 检验授权凭证是否有效
	 * @param string $access_token
	 * @param string $openid
	 * @return boolean 是否有效
	 */
	public function getOauthAuth($access_token,$openid){
	    $result = $this->http_get(self::OAUTH_AUTH_URL.'access_token='.$access_token.'&openid='.$openid);
	    if ($result)
	    {
	        $json = json_decode($result,true);
	        if (!$json || !empty($json['errcode'])) {
	            $this->errCode = $json['errcode'];
	            $this->errMsg = $json['errmsg'];
	            return false;
	        } else
	          if ($json['errcode']==0) return true;
	    }
	    return false;
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
		$result = $this->http_post(self::PAY_DELIVERNOTIFY.'access_token='.$this->access_token,self::json_encode($postdata));
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
		$result = $this->http_post(self::PAY_ORDERQUERY.'access_token='.$this->access_token,self::json_encode($postdata));
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
	
	/**
	 * 发送模板消息
	 * @param array $data 消息结构
	 * ｛
			"touser":"OPENID",
			"template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
			"url":"http://weixin.qq.com/download",
			"topcolor":"#FF0000",
			"data":{
				"参数名1": {
					"value":"参数",
					"color":"#173177"	 //参数颜色
					},
				"Date":{
					"value":"06月07日 19时24分",
					"color":"#173177"
					},
				"CardNumber":{
					"value":"0426",
					"color":"#173177"
					},
				"Type":{
					"value":"消费",
					"color":"#173177"
					}
			}
		}
	 * @return boolean|array
	 */
	public function sendTemplateMessage($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::TEMPLATE_SEND_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
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
	 * 获取多客服会话记录
	 * @param array $data 数据结构{"starttime":123456789,"endtime":987654321,"openid":"OPENID","pagesize":10,"pageindex":1,}
	 * @return boolean|array
	 */
	public function getCustomServiceMessage($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::CUSTOM_SERVICE_GET_RECORD.'access_token='.$this->access_token,self::json_encode($data));
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
	 * 转发多客服消息
	 * Examle: $obj->transfer_customer_service($customer_account)->reply();
	 * @param string $customer_account 转发到指定客服帐号：test1@test
	 */
	public function transfer_customer_service($customer_account = '')
	{
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'CreateTime'=>time(),
			'MsgType'=>'transfer_customer_service',
		);
		if (!$customer_account) {
			$msg['TransInfo'] = array('KfAccount'=>$customer_account);
		}
		$this->Message($msg);
		return $this;
	}
	
	/**
	 * 获取多客服客服基本信息
	 * 
	 * @return boolean|array
	 */
	public function getCustomServiceKFlist(){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::CUSTOM_SERVICE_GET_KFLIST.'access_token='.$this->access_token);
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
	 * 获取多客服在线客服接待信息
	 * 
	 * @return boolean|array {
	 "kf_online_list": [
	 {
	 "kf_account": "test1@test",	//客服账号@微信别名
	 "status": 1,			//客服在线状态 1：pc在线，2：手机在线,若pc和手机同时在线则为 1+2=3
	 "kf_id": "1001",		//客服工号
	 "auto_accept": 0,		//客服设置的最大自动接入数
	 "accepted_case": 1		//客服当前正在接待的会话数
	 }
	 ]
	 }
	 */
	public function getCustomServiceOnlineKFlist(){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::CUSTOM_SERVICE_GET_ONLINEKFLIST.'access_token='.$this->access_token);
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
}
