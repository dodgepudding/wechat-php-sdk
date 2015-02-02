<?php
/**
 *    微信公众平台PHP-SDK, 简单缓存实例
 *  @author binsee@163.com
 *  @link https://github.com/binsee/wechat-php-sdk
 *  @version 0.1
 *  usage:
 *   $options = array(
 *            'token'=>'tokenaccesskey', //填写你设定的key
 *            'encodingaeskey'=>'encodingaeskey', //填写加密用的EncodingAESKey
 *            'appid'=>'wxdk1234567890', //填写高级调用功能的app id
 *            'cachedir'=>'./cache/', //填写缓存目录，默认为当前运行目录的子目录cache下
 *            'logfile'=>'run.log' //填写日志输出文件，可选项。如果没有提供logcallback回调，且设置了输出文件则将日志输出至此文件，如果省略则不输出
 *        );
 *     $weObj = new EasyWechat($options);
 *   $weObj->valid();
 *   ...
 *
 */
class EasyWechat extends Wechat
{
    private $cachedir = '';
    private $logfile = '';

    public function __construct($options)
    {
        $this->cachedir = isset($options['cachedir']) ? dirname($options['cachedir'].'/.cache') . '/' : 'cache/';
        $this->logfile = isset($options['logfile']) ? $options['logfile'] : '';
        if ($this->cachedir) $this->checkDir($this->cachedir);
        parent::__construct($options);
    }

    /**
     * log overwrite
     * @param string|array $log
     */
    protected function log($log){
        if (is_array($log)) $log = print_r($log,true);
        if ($this->debug) {
            if (function_exists($this->logcallback)) {
                return call_user_func($this->logcallback,$log);
            }elseif ($this->logfile) {
                return file_put_contents($this->logfile, $log."\n", FILE_APPEND) > 0 ? true : false;
            }
        }
        return false;
    }

    /**
     * 重载设置缓存
     * @param string $cachename
     * @param mixed $value
     * @param int $expired 缓存秒数，如果为0则为长期缓存
     * @return boolean
     */
    protected function setCache($cachename,$value,$expired=0){
        $file = $this->cachedir . $cachename . '.cache';
        $data = array(
                'value' => $value,
                'expired' => $expired ? time() + $expired : 0
        );
        return file_put_contents( $file, serialize($data) ) ? true : false;
    }

    /**
     * 重载获取缓存
     * @param string $cachename
     * @return mixed
     */
    protected function getCache($cachename){
        $file = $this->cachedir . $cachename . '.cache';
        if (!is_file($file)) {
           return false;
        }
        $data = unserialize(file_get_contents( $file ));
        if (!is_array($data) || !isset($data['value']) || (!empty($data['value']) && $data['expired']<time())) {
            @unlink($file);
            return false;
        }
        return $data['value'];
    }

    /**
     * 重载清除缓存
     * @param string $cachename
     * @return boolean
     */
    protected function removeCache($cachename){
        $file = $this->cachedir . $cachename . '.cache';
        if ( is_file($file) ) {
            @unlink($file);
        }
        return true;
    }

    private function checkDir($dir, $mode=0777) {
        if (!$dir)  return false;
        if(!is_dir($dir)) {
            if (!file_exists($dir) && @mkdir($dir, $mode, true))
                return true;
            return false;
        }
        return true;
    }
}



