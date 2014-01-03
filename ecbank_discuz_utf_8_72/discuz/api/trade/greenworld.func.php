<?php
define('IN_DISCUZ', true);
	include_once '../../config.inc.php';
	require_once '../../include/global.func.php';
	require_once '../../include/db_'.$database.'.class.php';
	$uid = $_GET['uid'];
	$buyer = $_GET['buyer'];
	$price = $_GET['price'];
	$amount = $_GET['amount'];
	$orderid = $_GET['orderid'];
	$submitdate = $_GET['submitdate'];
	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
	$db->select_db($dbname);
	$settings = array();
	$query = $db->query("SELECT * FROM {$tablepre}settings");
	while($setting = $db->fetch_array($query)) {
		$settings[$setting['variable']] = $setting['value'];
	}
	$settings['creditstrans'] = explode(',', $settings['creditstrans']);
	$extcredits = $settings['creditstrans'][0];
	updatecredits($uid, array($extcredits => $amount));
	$timestamp = time();
	$db->query("INSERT INTO {$tablepre}creditslog (uid, fromto, sendcredits, receivecredits, send, receive, dateline, operation)VALUES ($uid, '$buyer', 0, $extcredits, 0, $amount, $timestamp, 'AFD')");
	$db->query("UPDATE {$tablepre}orders SET status = '2', confirmdate = $timestamp WHERE orderid = '$orderid'");
?>