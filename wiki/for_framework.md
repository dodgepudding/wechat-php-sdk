#为开发框架进行适配

为不同的开发框架进行适配缓存操作(保存access_token、jsapi_ticket)，及输出调试日志。

由于微信api需要缓存access_token与jsapi_ticket，而在不同框架下的缓存方式不同，所以原先在Wechat.class.php和QYWechat.class.php中缓存代码做了TODO标志。
需要各位在使用不同框架时再进行修改，但确实很麻烦，因为对结构进行了修改。

>取消了原先同步维护的Thinkphp版本，为Wechat类增加操作缓存3个重载方法`setCache`, `getCache`, `removeCache`，以及修改`log`方法可以重载。
>分别来实现在不同开发框架下的设置缓存、读取缓存、清除缓存、日志输出4个功能。  

在不同的开发框架下使用Wechat类库，请继承Wechat类，根据需要实现这4个方法。  
可参考Thinkphp版的`TPWechat.class.php`为不同框架进行适配。
欢迎提交其他框架的适配文件到项目库来。  

为Thinkphp进行适配的示例如下：
```php
/**
 *	微信公众平台PHP-SDK, ThinkPHP实例
 *  @author dodgepudding@gmail.com
 *  @link https://github.com/dodgepudding/wechat-php-sdk
 *  @version 1.2
 *  usage:
 *   $options = array(
 *			'token'=>'tokenaccesskey', //填写你设定的key
 *			'encodingaeskey'=>'encodingaeskey', //填写加密用的EncodingAESKey
 *			'appid'=>'wxdk1234567890', //填写高级调用功能的app id
 *			'appsecret'=>'xxxxxxxxxxxxxxxxxxx' //填写高级调用功能的密钥
 *		);
 *	 $weObj = new TPWechat($options);
 *   $weObj->valid();
 *   ...
 *  
 */
class TPWechat extends Wechat
{
	/**
	 * log overwrite
	 * @see Wechat::log()
	 */
	protected function log($log){
		if ($this->debug) {
			if (function_exists($this->logcallback)) {
				if (is_array($log)) $log = print_r($log,true);
				return call_user_func($this->logcallback,$log);
			}elseif (class_exists('Log')) {
				Log::write('wechat：'.$log, Log::DEBUG);
			}
		}
		return false;
	}
	
	/**
	 * 重载设置缓存
	 * @param string $cachename
	 * @param mixed $value
	 * @param int $expired
	 * @return boolean
	 */
	protected function setCache($cachename,$value,$expired){
		return S($cachename,$value,$expired);
	}
	
	/**
	 * 重载获取缓存
	 * @param string $cachename
	 * @return mixed
	 */
	protected function getCache($cachename){
		return S($cachename);
	}
	
	/**
	 * 重载清除缓存
	 * @param string $cachename
	 * @return boolean
	 */
	protected function removeCache($cachename){
		return S($cachename,null);
	}
}
```