<?php
require '../../source/class/class_core.php';
$discuz = C::app();
$discuz->init();
$arr = C::t('common_plugin')->fetch_by_identifier('gw');
$pluginid = $arr['pluginid'];
foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($pluginid) as $plugin) {
	$vars[$plugin['variable']] = $plugin['value'];
}
$ecpay_chk = $vars['stage_ecpay_chk'];
$TOkSi = $_REQUEST['process_time'] + $_REQUEST['gwsr'] + $_REQUEST['amount'];
$my_spcheck = gwSpcheck($ecpay_chk,$TOkSi);
$amt = 0;
$query = DB::query("SELECT * FROM ".DB::table('forum_order')." where orderid = '".$orderid."'");
while($order_rec = DB::fetch($query)) {
	$amt = $order_rec['amount'];
}
$res_str = "<center><div style='background-color:white'>";
if(($my_spcheck == $_REQUEST['spcheck'] && $_REQUEST['succ']=='1') && $amt == $amount) {
	insert($_REQUEST['od_sob']);
	$res_str .= "<FONT COLOR='green'>交易成功";
}else{
	$res_str .= "<FONT COLOR='red'>交易失敗";
}
$home = strrpos($_SERVER['REQUEST_URI'],'/api');
$cur_url = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, $home);
$res_str .= "</FONT>　<a href='" . $cur_url . "'>回首頁</a></div></center>";
echo $res_str;
function gwSpcheck($s,$U) {
	$a = substr($U,0,1).substr($U,2,1).substr($U,4,1);
	$b = substr($U,1,1).substr($U,3,1).substr($U,5,1);
	$c = ( $s % $U ) + $s + $a + $b;
	return $c;
}
function insert($od_sob) {
		$settings = array();
		$arr = C::t('common_setting')->fetch('creditstrans');
		$settings['creditstrans'] = $arr['svalue'];
		$settings['creditstrans'] = explode(',', $settings['creditstrans']);
		$extcredits = $settings['creditstrans'][0];
		$arr = C::t('forum_order')->fetch($od_sob);
		$uid = $arr['uid'];
		$price = $arr['price'];
		$orderid = $arr['orderid'];
		$buyer = $arr['buyer'];
		$arr = C::t('common_member_count')->fetch($uid);
		$old_credits = $arr['extcredits'.$extcredits];
		C::t('common_member_count')->update($uid, array('extcredits'.$extcredits=>($old_credits+$price)));
		C::t('common_credit_log')->insert(
			array('uid' => $uid, 
				'operation' => 'AFD', 
				'relatedid' => $uid, 
				'dateline' => time(), 
				'extcredits'.$extcredits => $price
			)
		);
		$timestamp = time();
		C::t('forum_creditslog')->insert(
			array(
				'uid' => $uid,
				'fromto' => $buyer,
				'sendcredits' => 0,
				'receivecredits' => $extcredits,
				'send' => 0,
				'receive' => $price,
				'dateline' => $timestamp,
				'operation' => 'AFD'
			)
		);
		C::t('forum_order')->update($orderid, array('status' => 2, 'confirmdate' => $timestamp));
}
?>