<?php
/**
 * 微信公共接口测试
 *
 */
include("../wechat.class.php");

function logdebug($text){
	file_put_contents('../data/log.txt',$text."\n",FILE_APPEND);
};
$options = array(
	'token'=>'tokenaccesskey', //填写你设定的key
	'debug'=>true,
	'logcallback'=>'logdebug'
);
$weObj = new Wechat($options);

// check null $scene_id
$qrcode = $weObj->getQRCode();
if ($qrcode != false) {
	echo "test failed.\n";
	die();
}

// check bad $type
$qrcode = $weObj->getQRCode(123, -1);
if ($qrcode != false) {	echo "test failed.\n";	die();}

// check bad $type
$qrcode = $weObj->getQRCode(123, 5);
if ($qrcode != false) {	echo "test failed.\n";	die();}

// check bad $scene_id
$qrcode = $weObj->getQRCode('ad', 0);
if ($qrcode != false) {	echo "test failed.\n";	die();}

// check bad $scene_id
$qrcode = $weObj->getQRCode('ad', 1);
if ($qrcode != false) {	echo "test failed.\n";	die();}

// check bad $scene_id
$qrcode = $weObj->getQRCode(123, 2);
if ($qrcode != false) {	echo "test failed.\n";	die();}

echo "test passed.\n";
