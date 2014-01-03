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
$prd_desc = $_POST['prd_desc'];
$imer_id = $_POST['imer_id'];
$i_invoice = $_POST['i_invoice'];
$nvpStr ='mer_id='.$mer_id.
	'&payment_type='.$payment_type.
	'&enc_key='.$enc_key.
	'&od_sob='.$od_sob.
	'&amt='.intval($amt).
	'&ok_url='.rawurlencode($ok_url).
	'&prd_desc='.$prd_desc;
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
	  $res_str.="<FONT COLOR='green'>[統一超商7-Eleven]超商繳費代碼:(<font color=blue size=+2>".$res['payno']."</font>)<br><br>";
		$res_str.="<a href=http://www.ecbank.com.tw/expenses-ibon.htm target=_blank>統一超商7-Eleven ibon 門市操作步驟</a><br>";
		$res_str.="請記下上列超商繳費代碼,至最近之統一超商7-Eleven便利商店,操作代碼繳費機台, 於列印出有條碼之繳款單後,至櫃台支付,<br>便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程";
		$res_str.="<br><br>本線上金流機制採用&lt; <a href=http://www.ecbank.com.tw target=_blank>綠界科技 ECBank 線上支付平台</a> &gt;";
		$res_str.="<br>綠界 ECBank交易單號:".$res['tsr'];
		include_once 'order_yes.php';
	}
} else {
	$res_str.= "<FONT COLOR='red'>取號失敗";
}
$res_str .= "</FONT></div></center>";
echo $res_str;
?>