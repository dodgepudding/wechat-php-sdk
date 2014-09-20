<?php
include "../qywechat.class.php";

function logg($text){
    file_put_contents('./log.txt',$text."\r\n\r\n",FILE_APPEND);
};

$options = array(
        'token'=>'9xxxxxxxxxxxx',	//填写应用接口的Token
        'encodingaeskey'=>'d4oxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',//填写加密用的EncodingAESKey
        'appid'=>'wxa0xxxxxxxxxx',	//填写高级调用功能的appid
        'debug'=>true,
        'logcallback'=>'logg'

);
logg("GET参数为：\n".var_export($_GET,true));
$weObj = new Wechat($options);
$ret=$weObj->valid();
if (!$ret) {
	logg("验证失败！");
	var_dump($ret);
	exit;
}
$f = $weObj->getRev()->getRevFrom();
$t = $weObj->getRevType();
$d = $weObj->getRevData();
$weObj->text("你好！来自星星的：".$f."\n你发送的".$t."类型信息：\n原始信息如下：\n".var_export($d,true))->reply();
logg("-----------------------------------------");
?>