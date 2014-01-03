<?php
$mer_id = $_POST['mer_id'];
$enc_key = $_POST['enc_key'];
$od_sob = $_POST['od_sob'];
$amt = $_POST['price'];
$ok_url = $_POST['ok_url'];
$payment_type = $_POST['payment_type'];
$expire_day = $_POST['expire_day'];
$nvpStr ='mer_id='.$mer_id.
	'&payment_type='.$payment_type.
	'&enc_key='.$enc_key.
	'&od_sob='.$od_sob.
	'&amt='.intval($amt).
	'&expire_day='.$expire_day.
	'&ok_url='.rawurlencode($ok_url);
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
		$res_str .= "<FONT COLOR='red'>取號錯誤".$res['error'];
	}else {
		$lineurl = "https://ecbank.com.tw/order/barcode_print.php?mer_id=".$res['mer_id']."&tsr=".$res['tsr']."";
	  $res_str.= '<FONT COLOR="green">訂單編號: '.$res['od_sob'].'<br>';
		$res_str.= '請列印超商條碼帳單至超商繳費 [<a href='.$lineurl.' target=_blank>點此列印</a>]';
		$res_str.="<br>綠界 ECBank交易單號:".$res['tsr'];
		include_once 'order_yes.php';
	}
} else {
	$res_str .= "<FONT COLOR='red'>取號失敗";
}
$res_str .= "</FONT></div></center>";
echo $res_str;
?>