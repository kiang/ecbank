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
$orderid = $_REQUEST['orderid'];
$status = 1;
$buyer = $_REQUEST['buyer'];
$uid = $_REQUEST['uid'];
$amount = $_REQUEST['amount'];
$price = $_REQUEST['price'];
$submitdate = $_REQUEST['submitdate'];

DB::insert('forum_order', array(
		'uid' => $uid,
		'orderid' => $orderid,
		'buyer' => $buyer,
		'amount' => $amount,
		'price' => $price,
		'submitdate' => $submitdate,
		'email' => $email,
		'ip' => $ip,
		'status' => "1",
		'admin' => "admin",
		'confirmdate' => "0"
	), false, true);
$chk = $vars['ecpay_chk'];
function gwSpcheck($s,$U) {
	$a = substr($U,0,1).substr($U,2,1).substr($U,4,1);
	$b = substr($U,1,1).substr($U,3,1).substr($U,5,1);
	$c = ( $s % $U ) + $s + $a + $b;
	return $c;
}
$TOkSi = $_REQUEST['process_time'] + $_REQUEST['gwsr'] + $_REQUEST['amount'];
$my_spcheck = gwSpcheck($chk,$TOkSi);
$amt = 0;
$query = DB::query("SELECT * FROM ".DB::table('forum_order')." where orderid = '".$orderid."'");
while($order_rec = DB::fetch($query)) {
	$amt = $order_rec['amount'];
}
$res_str = "<center><div style='background-color:white'>";
if(($my_spcheck == $_REQUEST['spcheck'] || $_REQUEST['succ']=='1') && $amt == $amount) {
	include_once 'greenworld.func.php';
	$res_str .= "<FONT COLOR='red'>success";
}else{
	$res_str .= "<FONT COLOR='red'>¥æ©ö¥¢±Ñ";
}

$res_str .= "</FONT></div></center>";
echo $res_str;

?>