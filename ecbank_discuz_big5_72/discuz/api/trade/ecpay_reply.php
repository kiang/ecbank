<?php
define('IN_DISCUZ', true);
include_once '../../config.inc.php';
require_once '../../include/global.func.php';
require_once '../../include/db_'.$database.'.class.php';
include_once '../../forumdata/cache/plugin_greenworld.php';
$chk = $_DPLUGIN['greenworld']['vars']['ecpay_chk'];
function gwSpcheck($s,$U) {
	$a = substr($U,0,1).substr($U,2,1).substr($U,4,1);
	$b = substr($U,1,1).substr($U,3,1).substr($U,5,1);
	$c = ( $s % $U ) + $s + $a + $b;
	return $c;
}
$TOkSi = $_REQUEST['process_time'] + $_REQUEST['gwsr'] + $_REQUEST['amount'];
$my_spcheck = gwSpcheck($chk,$TOkSi);
$res_str = "<center><div style='background-color:white'>";
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
$db->select_db($dbname);
$orderid = $_REQUEST['orderid'];
$status = 1;
$buyer = $_REQUEST['buyer'];
$uid = $_REQUEST['uid'];
$amount = $_REQUEST['amount'];
$price = $_REQUEST['price'];
$submitdate = $_REQUEST['submitdate'];
$db->query("INSERT INTO {$tablepre}orders (orderid, status, buyer, admin, uid, amount, price, submitdate, confirmdate)VALUES ('$orderid', '$status', '$buyer', 'admin', $uid, $amount, $price, $submitdate, 0)");
$amt = 0;
$query = $db->query("SELECT * FROM {$tablepre}orders WHERE orderid = '$orderid'");
while($t = $db->fetch_array($query)) {
	$amt = $t['amount'];
}
if(($my_spcheck == $_REQUEST['spcheck'] || $_REQUEST['succ']=='1') && $amount == $amt) {
	include_once 'greenworld.func.php';
	$res_str .= "<FONT COLOR='green'>交易成功";
}else{
	$res_str .= "<FONT COLOR='red'>交易失敗";
}
$res_str .= "</FONT></div></center>";
echo $res_str;
?>