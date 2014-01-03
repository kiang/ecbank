<?php
define('IN_DISCUZ', true);
require_once '../../source/class/class_core.php';
require_once '../../config/config_global.php';
require_once '../../config/config_ucenter.php';
require_once '../../source/function/function_core.php';
$discuz = & discuz_core::instance();
$discuz->init_cron = false;
$discuz->init_session = false;
$discuz->init();
$query = DB::query("SELECT pluginid,available FROM ".DB::table('common_plugin')." WHERE identifier = 'greenworld' ");
while($setting = DB::fetch($query)) {
	$available = $setting['available'];
	$pluginid = $setting['pluginid'];
}
$vars = array();
$query = DB::query("SELECT * FROM ".DB::table('common_pluginvar')." WHERE pluginid=$pluginid ");
while($plugin = DB::fetch($query)) {
	$vars[$plugin['variable']] = $plugin['value'];
}
$mid = $vars['ecbank_id'];
$pkey = $vars['ecbank_pkey'];
$checkcode = $pkey;
if($_REQUEST['payment_type'] == 'alipay'){
    $serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr'].$_REQUEST['od_sob'].$_REQUEST['amt']);
}else{
    $serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
}
$mac = trim($_REQUEST['mac']);
$tac = trim($_REQUEST['tac']);
if($_REQUEST['payment_type'] == 'alipay'){
    $ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid_new.php';
}else{
    $ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';
}
if($_REQUEST['payment_type'] == 'alipay'){
    $post_parm = 'key='.$checkcode.
                 '&serial='.$serial.
                 '&mac='.$mac;
}else{
    $post_parm	='key='.$checkcode.
                 '&serial='.$serial.
                 '&tac='.$tac;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$post_parm);
$strAuth = curl_exec($ch);
if (curl_errno($ch)){
	$strAuth = false;
}
curl_close($ch);
if($strAuth == 'valid=1'){
	if($_REQUEST['succ']=='1') {
		echo "OK";
		include_once 'greenworld.func.php';
		switch($_REQUEST['payment_type']){
		case 'paypal':
		case 'web_atm':
			$url = "http://".$_SERVER['HTTP_HOST'];
			header("Location:".$url);
			exit;
			break;
		default:
			break;
		}
	}else{
		echo "Failure";
	}
}else{
	echo "Illegal";
}
?>