<?php
/**
 *	微信公众平台PHP-SDK
 *  Wechatext为非官方微信发送API
 *  注: 用户id为通过getMsg()方法获取的FakeId值
 *  主要实现如下功能:
 *  send($id,$content) 向某用户id发送微信文字信息
 *  sendNews($id,$msgid) 发送图文消息
 *  getNewsList($page,$pagesize) 获取图文信息列表
 *  uploadFile($filepath,$type) 上传附件,包括图片/音频/视频
 *  getFileList($type,$page,$pagesize) 获取素材库文件列表
 *  sendImage($id,$fid) 发送图片消息
 *  sendAudio($id,$fid) 发送音频消息
 *  sendVideo($id,$fid) 发送视频消息
 *  getInfo($id) 根据id获取用户资料
 *  getNewMsgNum($lastid) 获取从$lastid算起新消息的数目
 *  getTopMsg() 获取最新一条消息的数据, 此方法获取的消息id可以作为检测新消息的$lastid依据
 *  getMsg($lastid,$offset=0,$perpage=50,$day=0,$today=0,$star=0) 获取最新的消息列表, 列表将返回消息id, 用户id, 消息类型, 文字消息等参数
 *  消息返回结构:  {"id":"消息id","type":"类型号(1为文字,2为图片,3为语音)","fileId":"0","hasReply":"0","fakeId":"用户uid","nickName":"昵称","dateTime":"时间戳","content":"文字内容"} 
 *  getMsgImage($msgid,$mode='large') 若消息type类型为2, 调用此方法获取图片数据
 *  getMsgVoice($msgid) 若消息type类型为3, 调用此方法获取语音数据
 *  @author dodge <dodgepudding@gmail.com>
 *  @link https://github.com/dodgepudding/wechat-php-sdk
 *  @version 1.2
 *  
 */

include "snoopy.class.php";
class Wechatext
{
	private $cookie;
	private $_cookiename;
	private $_cookieexpired = 3600;
	private $_account;
	private $_password;
	private $_datapath = './data/cookie_';
	private $debug;
	private $_logcallback;
	private $_token;
	
	public function __construct($options)
	{
		$this->_account = isset($options['account'])?$options['account']:'';
		$this->_password = isset($options['password'])?$options['password']:'';
		$this->_datapath = isset($options['datapath'])?$options['datapath']:$this->_datapath;
		$this->debug = isset($options['debug'])?$options['debug']:false;
		$this->_logcallback = isset($options['logcallback'])?$options['logcallback']:false;
		$this->_cookiename = $this->_datapath.$this->_account;
		$this->cookie = $this->getCookie($this->_cookiename);
	}

	/**
	 * 主动发消息
	 * @param  string $id      用户的uid(即FakeId)
	 * @param  string $content 发送的内容
	 */
	public function send($id,$content)
	{
		$send_snoopy = new Snoopy; 
		$post = array();
		$post['tofakeid'] = $id;
		$post['type'] = 1;
		$post['token'] = $this->_token;
		$post['content'] = $content;
		$post['ajax'] = 1;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/singlemsgpage?fromfakeid={$id}&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN";
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$submit = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
		$send_snoopy->submit($submit,$post);
		$this->log($send_snoopy->results);
		return $send_snoopy->results;
	}
	
	/**
	 * 获取图文信息列表
	 * @param $page 页码(从0开始)
	 * @param $pagesize 每页大小
	 * @return array
	 */
	public function getNewsList($page,$pagesize=10) {
		$send_snoopy = new Snoopy;
		$t = time().strval(mt_rand(100,999));
		$type=10;
		$post = array();
		$post['token'] = $this->_token;
		$post['ajax'] = 1;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-upload&lang=zh_CN&type=2&formId=1";
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$submit = "https://mp.weixin.qq.com/cgi-bin/operate_appmsg?token=".$this->_token."&lang=zh_CN&sub=list&t=ajax-appmsgs-fileselect&type=$type&r=".str_replace(' ','',microtime())."&pageIdx=$page&pagesize=$pagesize&subtype=3&formid=file_from_".$t;
		$send_snoopy->submit($submit,$post);
		$result = $send_snoopy->results;
		$this->log('newslist:'.$result);
		return json_decode($result,true);
	}
	
	/**
	 * 发送图文信息,必须从图文库里选取消息ID发送
	 * @param  string $id      用户的uid(即FakeId)
	 * @param  string $msgid 图文消息id
	 */
	public function sendNews($id,$msgid)
	{
		$send_snoopy = new Snoopy; 
		$post = array();
		$post['tofakeid'] = $id;
		$post['type'] = 10;
		$post['token'] = $this->_token;
		$post['fid'] = $msgid;
		$post['appmsgid'] = $msgid;
		$post['error'] = 'false';
		$post['ajax'] = 1;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/singlemsgpage?fromfakeid={$id}&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN";
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$submit = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
		$send_snoopy->submit($submit,$post);
		$this->log($send_snoopy->results);
		return $send_snoopy->results;
	}
	
	/**
	 * 上传附件(图片/音频/视频)
	 * @param string $filepath 本地文件地址
	 * @param int $type 文件类型: 2:图片 3:音频 4:视频
	 */
	public function uploadFile($filepath,$type=2) {
		$send_snoopy = new Snoopy;
		$send_snoopy->referer = "http://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-upload&lang=zh_CN&type=2&formId=1";
		$t = time().strval(mt_rand(100,999));
		$post = array('formId'=>'');
		$postfile = array('uploadfile'=>$filepath);
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->set_submit_multipart();
		$submit = "http://mp.weixin.qq.com/cgi-bin/uploadmaterial?cgi=uploadmaterial&type=$type&token=".$this->_token."&t=iframe-uploadfile&lang=zh_CN&formId=	file_from_".$t;
		$send_snoopy->submit($submit,$post,$postfile);
		$tmp = $send_snoopy->results;
		$this->log('upload:'.$tmp);
		preg_match("/formId,.*?\'(\d+)\'/",$tmp,$matches);
		if (isset($matches[1])) {
			return $matches[1];
		}
		return false;
	}
	
	/**
	 * 发送媒体文件
	 * @param $id 用户的uid(即FakeId)
	 * @param $fid 文件id
	 * @param $type 文件类型
	 */
	public function sendFile($id,$fid,$type) {
		$send_snoopy = new Snoopy; 
		$post = array();
		$post['tofakeid'] = $id;
		$post['type'] = $type;
		$post['token'] = $this->_token;
		$post['fid'] = $fid;
		$post['fileid'] = $fid;
		$post['error'] = 'false';
		$post['ajax'] = 1;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/singlemsgpage?fromfakeid={$id}&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN";
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$submit = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
		$send_snoopy->submit($submit,$post);
		$result = $send_snoopy->results;
		$this->log('sendfile:'.$result);
		$json = json_decode($result,true);
		if ($json && $json['ret']==0) 
			return true;
		else
			return false;
	}
	
	/**
	 * 获取素材库文件列表
	 * @param $type 文件类型: 2:图片 3:音频 4:视频
	 * @param $page 页码(从0开始)
	 * @param $pagesize 每页大小
	 * @return array
	 */
	public function getFileList($type,$page,$pagesize=10) {
		$send_snoopy = new Snoopy;
		$t = time().strval(mt_rand(100,999));
		$post = array();
		$post['token'] = $this->_token;
		$post['ajax'] = 1;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-upload&lang=zh_CN&type=2&formId=1";
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$submit = "https://mp.weixin.qq.com/cgi-bin/filemanagepage?token=".$this->_token."&lang=zh_CN&t=ajax-fileselect&type=$type&r=".str_replace(' ','',microtime())."&pageIdx=$page&pagesize=$pagesize&formid=file_from_".$t;
		$send_snoopy->submit($submit,$post);
		$result = $send_snoopy->results;
		$this->log('filelist:'.$result);
		return json_decode($result,true);
	}
	
	/**
	 * 发送图文信息,必须从库里选取文件ID发送
	 * @param  string $id      用户的uid(即FakeId)
	 * @param  string $fid 文件id
	 */
	public function sendImage($id,$fid)
	{
		return $this->sendFile($id,$fid,2);
	}
	
	/**
	 * 发送语音信息,必须从库里选取文件ID发送
	 * @param  string $id      用户的uid(即FakeId)
	 * @param  string $fid 语音文件id
	 */
	public function sendAudio($id,$fid)
	{
		return $this->sendFile($id,$fid,3);
	}
	
	/**
	 * 发送视频信息,必须从库里选取文件ID发送
	 * @param  string $id      用户的uid(即FakeId)
	 * @param  string $fid 视频文件id
	 */
	public function sendVideo($id,$fid)
	{
		return $this->sendFile($id,$fid,4);
	}
	
	/**
	 * 发送预览图文消息
	 * @param string $account 账户名称
	 * @param string $title 标题
	 * @param string $summary 摘要
	 * @param string $content 内容
	 * @param string $photoid 素材库里的图片id(可通过uploadFile上传后获取)
	 * @param string $srcurl 原文链接
	 * @return json
	 */
	public function sendPreview($account,$title,$summary,$content,$photoid,$srcurl='') {
		$send_snoopy = new Snoopy;
		$submit = "https://mp.weixin.qq.com/cgi-bin/operate_appmsg?sub=preview&t=ajax-appmsg-preview";
		$send_snoopy->set_submit_normal();
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->referer = 'https://mp.weixin.qq.com/cgi-bin/operate_appmsg?sub=edit&t=wxm-appmsgs-edit-new&type=10&subtype=3&lang=zh_CN';
		$post = array(
				'AppMsgId'=>'',
				'ajax'=>1,
				'content0'=>$content,
				'count'=>1,
				'digest0'=>$summary,
				'error'=>'false',
				'fileid0'=>$photoid,
				'preusername'=>$account,
				'sourceurl0'=>$srcurl,
				'title0'=>$title,
		);
		$post['token'] = $this->_token;
		$send_snoopy->submit($submit,$post);
		$tmp = $send_snoopy->results;
		$this->log('step2:'.$tmp);
		$json = json_decode($tmp,true);
		return $json;
	}
	
	/**
	 * 获取用户的信息
	 * @param  string $id 用户的uid(即FakeId)
	 * @return array     {FakeId:100001,NickName:'昵称',Username:'用户名',Signature:'签名档',Country:'中国',Province:'广东',City:'广州',Sex:'1',GroupID:'0'}
	 */
	public function getInfo($id)
	{
		$send_snoopy = new Snoopy; 
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=".$this->_token;
		$submit = "https://mp.weixin.qq.com/cgi-bin/getcontactinfo?t=ajax-getcontactinfo&lang=zh_CN&fakeid=".$id;
		$post = array('ajax'=>1,'token'=>$this->_token);
		$send_snoopy->submit($submit,$post);
		$this->log($send_snoopy->results);
		$result = json_decode($send_snoopy->results,1);
		if(!$result){
			return false;
		}
		return $result;
	}

	/**
	 * 获取消息更新数目
	 * @param int $lastid 最近获取的消息ID,为0时获取总消息数目
	 * @return int 数目
	 */
	public function getNewMsgNum($lastid=0){
		$send_snoopy = new Snoopy; 
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=".$this->_token;
		$submit = "https://mp.weixin.qq.com/cgi-bin/getnewmsgnum?t=ajax-getmsgnum&lastmsgid=".$lastid;
		$post = array('ajax'=>1,'token'=>$this->_token);
		$send_snoopy->submit($submit,$post);
		$this->log($send_snoopy->results);
		$result = json_decode($send_snoopy->results,1);
		if(!$result){
			return false;
		}
		return intval($result['newTotalMsgCount']);
	}
	
	/**
	 * 获取最新一条消息
	 * @return array {"id":"最新一条id","type":"类型号(1为文字,2为图片,3为语音)","fileId":"0","hasReply":"0","fakeId":"用户uid","nickName":"昵称","dateTime":"时间戳","content":"文字内容","playLength":"0","length":"0","source":"","starred":"0","status":"4"}        
	 */
	public function getTopMsg(){
		$send_snoopy = new Snoopy; 
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=".$this->_token;
		$lastid = $lastid===0 ? '':$lastid;
		$submit = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=ajax-message&lang=zh_CN&count=1&timeline=0&day=0&star=0&cgi=getmessage&offset=0";
		$post = array('ajax'=>1,'token'=>$this->_token);
		$send_snoopy->submit($submit,$post);
		$this->log($send_snoopy->results);
		$result = json_decode($send_snoopy->results,1);
		if($result && count($result)>0)
			return $result[0];
		else 
			return false;
	}
	
	/**
	 * 获取新消息
	 * @param $lastid 传入最后的消息id编号,为0则从最新一条起倒序获取
	 * @param $offset lastid起算第一条的偏移量
	 * @param $perpage 每页获取多少条
	 * @param $day 最近几天消息(1:昨天,2:前天,3:五天内)
	 * @param $today 是否只显示今天的消息, 与$day参数不能同时大于0
	 * @param $star 是否星标组信息
	 * @return array[] 同getTopMsg()返回的字段结构相同
	 */
	public function getMsg($lastid=0,$offset=0,$perpage=50,$day=0,$today=0,$star=0){
		$send_snoopy = new Snoopy; 
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=".$this->_token;
		$lastid = $lastid===0 ? '':$lastid;
		$submit = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=ajax-message&lang=zh_CN&count=$perpage&timeline=$today&day=$day&star=$star&frommsgid=$lastid&cgi=getmessage&offset=$offset";
		$post = array('ajax'=>1,'token'=>$this->_token);
		$send_snoopy->submit($submit,$post);
		$this->log($send_snoopy->results);
		$result = json_decode($send_snoopy->results,1);
		if(!$result){
			return false;
		}
		return $result;
	}
	
	/**
	 * 获取图片消息
	 * @param int $msgid 消息id
	 * @param string $mode 图片尺寸(large/small)
	 * @return jpg二进制文件
	 */
	public function getMsgImage($msgid,$mode='large'){
		$send_snoopy = new Snoopy; 
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=".$this->_token;
		$url = "https://mp.weixin.qq.com/cgi-bin/getimgdata?token=".$this->_token."&msgid=$msgid&mode=$mode&source=&fileId=0";
		$send_snoopy->fetch($url);
		$result = $send_snoopy->results;
		$this->log('msg image:'.$msgid.';length:'.strlen($result));
		if(!$result){
			return false;
		}
		return $result;
	}
	
	/**
	 * 获取语音消息
	 * @param int $msgid 消息id
	 * @return mp3二进制文件
	 */
	public function getMsgVoice($msgid){
		$send_snoopy = new Snoopy; 
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=".$this->_token;
		$url = "https://mp.weixin.qq.com/cgi-bin/getvoicedata?token=".$this->_token."&msgid=$msgid&fileId=0";
		$send_snoopy->fetch($url);
		$result = $send_snoopy->results;
		$this->log('msg voice:'.$msgid.';length:'.strlen($result));
		if(!$result){
			return false;
		}
		return $result;
	}
	
	/**
	 * 模拟登录获取cookie
	 * @return [type] [description]
	 */
	public function login(){
		$snoopy = new Snoopy; 
		$submit = "https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
		$post["username"] = $this->_account;
		$post["pwd"] = md5($this->_password);
		$post["f"] = "json";
		$snoopy->submit($submit,$post);
		$cookie = '';
		$this->log($snoopy->headers);
		foreach ($snoopy->headers as $key => $value) {
			$value = trim($value);
			if(preg_match('/^set-cookie:[\s]+([^=]+)=([^;]+)/i', $value,$match))
				$cookie .=$match[1].'='.$match[2].'; ';
		}
		if ($cookie) {
			$send_snoopy = new Snoopy; 
			$send_snoopy->rawheaders['Cookie']= $cookie;
			$send_snoopy->maxredirs = 0;
			$url = "https://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-index&lang=zh_CN";
			$send_snoopy->fetch($url);
			$header = implode(',',$send_snoopy->headers);
			$this->log('header:'.print_r($send_snoopy->headers,true));
			preg_match("/token=(\d+)/i",$header,$matches);
			if($matches){
				$this->_token = $matches[1];
				$this->log('token:'.$this->_token);
			}
		}
		$this->saveCookie($this->_cookiename,$cookie);
		return $cookie;
	}

	/**
	 * 把cookie写入缓存
	 * @param  string $filename 缓存文件名
	 * @param  string $content  文件内容
	 * @return bool
	 */
	public function saveCookie($filename,$content){
		return file_put_contents($filename,$content);
	}

	/**
	 * 读取cookie缓存内容
	 * @param  string $filename 缓存文件名
	 * @return string cookie
	 */
	public function getCookie($filename){
		if (file_exists($filename)) {
			$mtime = filemtime($filename);
			if ($mtime<time()-$this->_cookieexpired) 
				$data = '';
			else
				$data = file_get_contents($filename);
		} else
			$data = '';
		if($data){
			$send_snoopy = new Snoopy; 
			$send_snoopy->rawheaders['Cookie']= $data;
			$send_snoopy->maxredirs = 0;
			$url = "https://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-index&lang=zh_CN";
			$send_snoopy->fetch($url);
			$header = implode(',',$send_snoopy->headers);
			$this->log('header:'.print_r($send_snoopy->headers,true));
			preg_match("/token=(\d+)/i",$header,$matches);
			if(empty($matches)){
				return $this->login();
			}else{
				$this->_token = $matches[1];
				$this->log('token:'.$this->_token);
				return $data;
			}
		}else{
			return $this->login();
		}
	}

	/**
	 * 验证cookie的有效性
	 * @return bool
	 */
	public function checkValid()
	{
		$send_snoopy = new Snoopy; 
		$post = array('ajax'=>1,'token'=>$this->_token);
		$submit = "https://mp.weixin.qq.com/cgi-bin/getregions?id=1017&t=ajax-getregions&lang=zh_CN";
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->submit($submit,$post);
		$result = $send_snoopy->results;
		if(json_decode($result,1)){
			return true;
		}else{
			return false;
		}
	}
	
	private function log($log){
		if ($this->debug && function_exists($this->_logcallback)) {
			if (is_array($log)) $log = print_r($log,true);
			return call_user_func($this->_logcallback,$log);
		}
	}
	
}
