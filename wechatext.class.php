<?php
/**
 *	微信公众平台PHP-SDK
 *  Wechatext为非官方微信发送API
 *  主要实现如下功能:
 *  send($id,$content) 向某用户id发送微信文字信息
 *  batch($ids,$content) 批量向一批用户发送微信文字信息
 *  sendNews($account,$title,$summary,$content,$pic,$srcurl='') 向一个微信账户发送图文信息
 *  @author dodge <dodgepudding@gmail.com>
 *  @link https://github.com/dodgepudding/wechat-php-sdk
 *  @version 1.1
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
	 * @param  string $id      用户的uid
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
	 * 批量发送
	 * @param  string $ids     多个用户的uid,逗号分割,通常一次不要超过20个
	 * @param  string $content 发送的内容
	 */
	public function batch($ids,$content)
	{
		$ids_array = explode(",", $ids);
		$result = array();
		foreach ($ids_array as $key => $value) {
			$send_snoopy = new Snoopy; 
			$post = array();
			$post['type'] = 1;
			$post['content'] = $content;
			$post['token'] = $this->_token;
			$post['ajax'] = 1;
            $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/singlemsgpage?fromfakeid={$value}&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN";
			$send_snoopy->rawheaders['Cookie']= $this->cookie;
			$submit = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
			$post['tofakeid'] = $value;
			$send_snoopy->submit($submit,$post);
			$tmp = $send_snoopy->results;
			$this->log($tmp);
			array_push($result, $tmp);
		}
		return $result;
	}	
	
	/**
	 * 发送图文消息
	 * @param string $account 账户名称
	 * @param string $title 标题
	 * @param string $summary 摘要
	 * @param string $content 内容
	 * @param string $pic 图片
	 * @param string $srcurl 原文链接
	 * @return json
	 */
	public function sendNews($account,$title,$summary,$content,$pic,$srcurl='') {
		$send_snoopy = new Snoopy;
		$send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-upload&lang=zh_CN&type=2&formId=1";
		$post = array('formId'=>'');
		$postfile = array('uploadfile'=>$pic);
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->set_submit_multipart();
		$submit = "https://mp.weixin.qq.com/cgi-bin/uploadmaterial?cgi=uploadmaterial&type=2&t=iframe-uploadfile&lang=zh_CN&formId=1";
		$send_snoopy->submit($submit,$post,$postfile);
		$tmp = $send_snoopy->results;
		$this->log($tmp);
		preg_match("/formId,.*?\'(\d+)\'/",$tmp,$matches);
		if (isset($matches[1])) {
			$photoid = $matches[1];
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
			$this->log($tmp);
			$json = json_decode($tmp,true);
			return $json;
		}
		return false;
	}
	
	/**
	 * 获取用户的信息
	 * @param  string $id 用户的uid
	 * @return [type]     [description]
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
			$this->login();
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
