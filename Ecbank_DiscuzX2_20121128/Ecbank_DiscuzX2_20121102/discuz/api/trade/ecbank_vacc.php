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
$mer_id = $_POST['mer_id'];
$enc_key = $_POST['enc_key'];
$od_sob = $_POST['od_sob'];
$amt = $_POST['price'];
$ok_url = $_POST['ok_url'];
$payment_type = $_POST['payment_type'];
$setbank = $_POST['setbank'];
$expire_day = $_POST['expire_day'];
$imer_id = $_POST['imer_id'];
$i_invoice = $_POST['i_invoice'];
$nvpStr ='mer_id='.$mer_id.
	'&payment_type='.$payment_type.
	'&setbank='.$setbank.
	'&enc_key='.$enc_key.
	'&od_sob='.$od_sob.
	'&amt='.intval($amt).
	'&expire_day='.$expire_day.
	'&ok_url='.rawurlencode($ok_url);
if($i_invoice == 'yes'){
    $nvpStr.='&inv_active=1'.
             '&inv_mer_id='.$imer_id.
             '&inv_amt='.intval($amt).
             '&inv_semail='.'test@test.net'.
             '&inv_delay=0';
    $nvpStr.='&prd_name[]=交易金額'.
             '&prd_qry[]=1'.
             '&prd_price[]='.intval($amt);
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,'https://ecbank.com.tw/gateway.php');
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpStr);
$strAuth = curl_exec($ch);
if (curl_errno($ch)) {
	$strAuth = false;
}
curl_close($ch);
$res_str = "<center><div style='background-color:white'>";
if($strAuth) {
	parse_str($strAuth, $res);
	if(!isset($res['error']) || $res['error'] != '0'){
		$res_str.= "<FONT COLOR='red'>取號錯誤".$res['error'];
	}else {
	  $res_str.="<FONT COLOR='green'>轉帳銀行代碼:(<font color=blue size=+2>".$res['bankcode']."</font>)";
		$res_str.="轉帳銀行帳戶:(<font color=blue size=+2>".$res['vaccno']."</font>)";
		$res_str.="<br>持玉山銀行金融卡轉帳可免付跨行交易手續費，其它銀行依該行跨行手續費規定扣繳<br><a href=https://netbank.esunbank.com.tw/webatm/> 玉山銀行 WEB-ATM https://netbank.esunbank.com.tw/webatm/</a>";
		$res_str.="<br>綠界 ECBank交易單號:".$res['tsr'];
		include_once 'order_yes.php';
	}
} else {
	$res_str.= "<FONT COLOR='red'>取號失敗";
}
$res_str .= "</FONT></div></center>";
echo $res_str;
?>