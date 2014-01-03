<?php
define('IN_DISCUZ', true);
include_once '../../forumdata/cache/plugin_greenworld.php';
$mid = $_DPLUGIN['greenworld']['vars']['ecbank_id'];
$pkey = $_DPLUGIN['greenworld']['vars']['ecbank_pkey'];
$checkcode = $pkey;
$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
$tac = trim($_REQUEST['tac']);
$ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';
$post_parm	=	'key='.$checkcode.
	'&serial='.$serial.
	'&tac='.$tac;
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