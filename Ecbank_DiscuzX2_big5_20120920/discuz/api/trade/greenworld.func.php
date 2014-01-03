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
$uid = $_GET['uid'];
$buyer = $_GET['buyer'];
$price = $_GET['price'];
$amount = $_GET['amount'];
$orderid = $_GET['orderid'];
$settings = array();
$query = DB::query("SELECT * FROM ".DB::table('common_setting')." where skey = 'creditstrans' ");
while($setting = DB::fetch($query)) {
	$settings[$setting['skey']] = $setting['svalue'];
}
$settings['creditstrans'] = explode(',', $settings['creditstrans']);
$extcredits = $settings['creditstrans'][0];
$query = DB::query("SELECT * FROM ".DB::table('common_member_count')." where uid = ".$uid);
while($extcredit = DB::fetch($query)) {
	$old_credits = $extcredit['extcredits'.$extcredits];
}
DB::update('common_member_count', array('extcredits'.$extcredits=>($old_credits+$amount)), array('uid' => $uid));
DB::insert('common_credit_log', array('uid' => $uid, 'operation' => 'AFD', 'relatedid' => $uid, 'dateline' => time(), 'extcredits'.$extcredits => $amount));
$timestamp = time();
DB::insert('forum_creditslog', array(
		'uid' => $uid,
		'fromto' => $buyer,
		'sendcredits' => 0,
		'receivecredits' => $extcredits,
		'send' => 0,
		'receive' => $amount,
		'dateline' => $timestamp,
		'operation' => 'AFD',
	), false, true);
DB::update('forum_order', array('status' => 2, 'confirmdate' => $timestamp), array('orderid' => $orderid));
?>