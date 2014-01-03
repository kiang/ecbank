<?php
require '../../source/class/class_core.php';
$discuz = C::app();
$discuz->init();
$arr = C::t('common_plugin')->fetch_by_identifier('gw');
$pluginid = $arr['pluginid'];
foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($pluginid) as $plugin) {
	$vars[$plugin['variable']] = $plugin['value'];
}
$ecbank_id = $vars['ecbank_id'];
$ecbank_key = $vars['ecbank_key'];
$price = $_POST['price'];
$amount = $_POST['amount'];
$payment_type = $_POST['payment_type'];
$expire_day = 3;
$discuz_uid = $_POST['uid'];
$discuz_user = $_POST['username'];
$email = $_POST['email'];
$ip = $_POST['ip'];
$url = $_POST['url'];
$od_sob = $discuz_uid . '_' . time();
$setbank = '';
$ecbank_reply_url = $ok_url =  $return_url =  $url."/api/trade/gw_response.php?od_sob=".$od_sob;
$ecpay_reply_url = $url."/api/trade/gw_ecpay_response.php?od_sob=".$od_sob;
$ecpay_stage_reply_url = $url."/api/trade/gw_stage_ecpay_response.php?od_sob=".$od_sob;
$allpay_reply_url = $url."/api/trade/gw_allpay_response.php?od_sob=".$od_sob;
$item_name = $item_desc = $prd_desc = $_G['setting']['bbname']. '充值';
$res_str = "<center><div style='background-color:white'>";
$ecpay_id = $vars['ecpay_id'];
$ecpay_chk = $vars['ecpay_chk'];
$stage_ecpay_id = $vars['stage_ecpay_id'];
$stage_ecpay_chk = $vars['stage_ecpay_chk'];
$allpay_id = $vars['allpay_id'];
$allpay_chk = $vars['allpay_chk'];
$stage3_rate = $vars['stage3_rate'];
$stage6_rate = $vars['stage6_rate'];
$stage12_rate = $vars['stage12_rate'];
switch($payment_type) {
	case 'vacc' :
	case 'barcode' :
	case 'cvs' :
	case 'ibon' :
	case 'web_atm' :
	case 'paypal' :
	case 'alipay' :
	case 'tenpay' :
		$error = err_set($ecbank_id, $ecbank_key);
		break;
	case 'stage0' :
		$error = err_set($ecpay_id, $ecpay_chk);
		break;
	case 'stage3' :
	case 'stage6' :
	case 'stage12' :
		$error = err_set($stage_ecpay_id, $stage_ecpay_chk);
		break;
	case 'allpay' :
		$error = err_set($allpay_id, $allpay_chk);
		break;
}
if($error) {
	$res_str.= "<FONT COLOR='red'>系統金流設定有誤，請聯絡系統管理者";
} else {
	switch($payment_type) {
		case 'vacc' :
			$setbank = 'ESUN';
		case 'barcode' :
		case 'cvs' :
		case 'ibon' :
			$nvpStr ='mer_id='.$ecbank_id.
				'&payment_type='.$payment_type.
				'&enc_key='.$ecbank_key.
				'&od_sob='.$od_sob.
				'&amt='.intval($price).
				'&expire_day='.$expire_day.
				'&setbank='.$setbank.
				'&ok_url='.rawurlencode($ok_url).
				'&return_url='.rawurlencode($return_url).
				'&item_desc='.$item_desc.
				'&prd_desc='.$prd_desc;
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
			if($strAuth) {
				parse_str($strAuth, $res);
				if(!isset($res['error']) || $res['error'] != '0'){
					$res_str.= "<FONT COLOR='red'>取號錯誤".$res['error'];
				}else {
					switch($payment_type) {
						case 'vacc' :
							$res_str.="<FONT COLOR='green'>轉帳銀行代碼:(<font color=blue size=+2>".$res['bankcode']."</font>)";
							$res_str.="轉帳銀行帳戶:(<font color=blue size=+2>".$res['vaccno']."</font>)";
							$res_str.="<br>持玉山銀行金融卡轉帳可免付跨行交易手續費，其它銀行依該行跨行手續費規定扣繳<br><a href=https://netbank.esunbank.com.tw/webatm/> 玉山銀行 WEB-ATM https://netbank.esunbank.com.tw/webatm/</a>";
							$res_str.="<br>綠界 ECBank交易單號:".$res['tsr'];
							break;
						case 'barcode' :
							$lineurl = "https://ecbank.com.tw/order/barcode_print.php?mer_id=".$res['mer_id']."&tsr=".$res['tsr']."";
							$res_str.= '<FONT COLOR="green">訂單編號: '.$res['od_sob'].'<br>';
							$res_str.= '請列印超商條碼帳單至超商繳費 [<a href='.$lineurl.' target=_blank>點此列印</a>]';
							$res_str.="<br>綠界 ECBank交易單號:".$res['tsr'];
							break;
						case 'cvs' :
							$res_str.="<FONT COLOR='green'>[全家/萊爾富/OK]超商繳費代碼:(<font color=blue size=+2>".$res['payno']."</font>)<br><br>";
							$res_str.="<a href=http://www.ecbank.com.tw/expenses-famiport.htm target=_blank>全家 FamiPort 門市操作步驟</a><br>";
							$res_str.="<a href=http://www.ecbank.com.tw/expenses-life-et.htm target=_blank>萊爾富Life-ET 門市操作步驟</a><br>";
							$res_str.="<a href=http://www.ecbank.com.tw/expenses-okgo.htm target=_blank>OK OKGO 門市操作步驟</a><br>";
							$res_str.="請記下上列超商繳費代碼,至最近之全家、OK或萊爾富便利商店,操作代碼繳費機台, 於列印出有調碼之繳款單後,至櫃台支付,<br>便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程";
							$res_str.="<br><br>本線上金流機制採用&lt; <a href=http://www.ecbank.com.tw target=_blank>綠界科技 ECBank 線上支付平台</a> &gt;, 於超商繳費單列印平台可見綠界科技 ECBank 圖示,請安心使用";
							$res_str.="<br>綠界 ECBank交易單號:".$res['tsr'];
							break;
						case 'ibon' :
							$res_str.="<FONT COLOR='green'>[統一超商7-Eleven]超商繳費代碼:(<font color=blue size=+2>".$res['payno']."</font>)<br><br>";
							$res_str.="<a href=http://www.ecbank.com.tw/expenses-ibon.htm target=_blank>統一超商7-Eleven ibon 門市操作步驟</a><br>";
							$res_str.="請記下上列超商繳費代碼,至最近之統一超商7-Eleven便利商店,操作代碼繳費機台, 於列印出有條碼之繳款單後,至櫃台支付,<br>便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程";
							$res_str.="<br><br>本線上金流機制採用&lt; <a href=http://www.ecbank.com.tw target=_blank>綠界科技 ECBank 線上支付平台</a> &gt;";
							$res_str.="<br>綠界 ECBank交易單號:".$res['tsr'];
							break;
					}
					
					insert($od_sob, $discuz_user, $discuz_uid, $amount, $price, $email, $ip);
				}
			} else {
				$res_str.= "<FONT COLOR='red'>取號失敗";
			}
			break;
		case 'paypal' :
		case 'web_atm' :
		case 'alipay' :
		case 'tenpay' :
			$can_insert = TRUE;
			$form = '<form action="https://ecbank.com.tw/gateway.php" method="post" name="online" id="online">';
			$form .= '<input type="hidden" name="mer_id" value="'. $ecbank_id .'" />';
			$form .= '<input type="hidden" name="enc_key" value="'. $ecbank_key .'" />';
			$form .= '<input type="hidden" name="payment_type" value="' . $payment_type .'" />';
			$form .= '<input type="hidden" name="od_sob" value="'. $od_sob . '" />';
			$form .= '<input type="hidden" name="amt" value="'. $price .'" />';
			
			switch ($payment_type) {
				case 'web_atm' :
					$form .= '<input type="hidden" name="return_url" value="'. $return_url .'" />';
					break;
				case 'paypal' :
					$form .= '<input type="hidden" name="item_name" value="'. $item_name .'" />';
					$form .= '<input type="hidden" name="cur_type" value="TWD" />';
					$form .= '<input type="hidden" name="cancel_url" value="' .$url .'" />';
					$form .= '<input type="hidden" name="return_url" value="'. $return_url .'" />';
					break;
				case 'alipay' :
					$upload_gateway = 'https://ecbank.com.tw/web_service/alipay_goods_upload.php';
					$post_str='enc_key='. $ecbank_key.
						  '&mer_id='. $ecbank_id.
						  '&type=upload_goods'.
						  '&goods_id=999'.
						  '&goods_title='. $item_name.
						  '&goods_price='. $price.
						  '&goods_href='. $url;
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,$upload_gateway);
					curl_setopt($ch, CURLOPT_VERBOSE, 1);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
					$strAuth = curl_exec($ch);
					if (curl_errno($ch)) {
						$strAuth = false;
					}
					curl_close($ch);
					parse_str($strAuth, $res);
					if($strAuth == 'state=NEW_SUCCESS' || $strAuth == 'state=MODIFY_SUCCESS') {
						$form .= '<input type="hidden" name="return_url" value="'. $url .'" />';
						$form .= '<input type="hidden" name="ok_url" value="'. $ok_url .'" />';
						$form .= '<input type="hidden" name="goods_name[]" value="999" />';
						$form .= '<input type="hidden" name="goods_amount[]" value="1" />';
					} else {
						$can_insert = FALSE;
						$res_str .= '<FONT COLOR="red">商品錯誤：'.$strAuth.' 請聯絡系統管理者!!<br>';
					}
					break;
				case 'tenpay' :
					$form .= '<input type="hidden" name="return_url" value="'. $url .'" />';
					$form .= '<input type="hidden" name="ok_url" value="'. $ok_url .'" />';
					break;
			}
			$form .= '</form>';
			if($can_insert) {
				$form .= '<script type="text/javascript" language="javascript">';
				$form .= 'var form = document.getElementById("online");';
				$form .= 'form.submit();';
				$form .= '</script>';
				echo $form;
				insert($od_sob, $discuz_user, $discuz_uid, $amount, $price, $email, $ip);
			}
			break;
		case 'stage0' :
		case 'stage3' :
		case 'stage6' :
		case 'stage12' :
		case 'allpay' :
			$amt = 0;
			switch($payment_type) {
				case 'stage0' :
					$form = '<form action="https://ecpay.com.tw/form_Sc_to5.php" method="post" name="online" id="online">';
					$form .= '<input type="hidden" name="client" value="'. $ecpay_id .'" />';
					$form .= '<input type="hidden" name="roturl" value="' . $ecpay_reply_url .'" />';
					$form .= '<input type="hidden" name="bk_posturl" value="' . $ecpay_reply_url .'" />';
					$amt = $price;
					break;
				case 'stage3' :
					$form = '<form action="https://ecpay.com.tw/form_Sc_to5_fn.php" method="post" name="online" id="online">';
					$form .= '<input type="hidden" name="client" value="'. $stage_ecpay_id .'" />';
					$form .= '<input type="hidden" name="stage" value="3" />';
					$form .= '<input type="hidden" name="roturl" value="' . $ecpay_stage_reply_url .'" />';
					$form .= '<input type="hidden" name="bk_posturl" value="' . $ecpay_stage_reply_url .'" />';
					$amt = ceil($price * (1 + $stage3_rate / 100));
					break;
				case 'stage6' :
					$form = '<form action="https://ecpay.com.tw/form_Sc_to5_fn.php" method="post" name="online" id="online">';
					$form .= '<input type="hidden" name="client" value="'. $stage_ecpay_id .'" />';
					$form .= '<input type="hidden" name="stage" value="6" />';
					$form .= '<input type="hidden" name="roturl" value="' . $ecpay_stage_reply_url .'" />';
					$form .= '<input type="hidden" name="bk_posturl" value="' . $ecpay_stage_reply_url .'" />';
					$amt = ceil($price * (1 + $stage6_rate / 100));
					break;
				case 'stage12' :
					$form = '<form action="https://ecpay.com.tw/form_Sc_to5_fn.php" method="post" name="online" id="online">';
					$form .= '<input type="hidden" name="client" value="'. $stage_ecpay_id .'" />';
					$form .= '<input type="hidden" name="stage" value="12" />';
					$form .= '<input type="hidden" name="roturl" value="' . $ecpay_stage_reply_url .'" />';
					$form .= '<input type="hidden" name="bk_posturl" value="' . $ecpay_stage_reply_url .'" />';
					$amt = ceil($price * (1 + $stage12_rate / 100));
					break;
				case 'allpay' :
					$form = '<form action="https://credit.allpay.com.tw/form_Sc_to5.php" method="post" name="online" id="online">';
					$form .= '<input type="hidden" name="client" value="'. $allpay_id .'" />';
					$form .= '<input type="hidden" name="email" value="' . $email . '" />';
					$form .= '<input type="hidden" name="roturl" value="' . $allpay_reply_url .'" />';
					$form .= '<input type="hidden" name="bk_posturl" value="' . $allpay_reply_url .'" />';
					$amt = $price;
					break;
			}
			
			$form .= '<input type="hidden" name="act" value="auth" />';
			
			$form .= '<input type="hidden" name="od_sob" value="'. $od_sob . '" />';
			$form .= '<input type="hidden" name="amount" value="'. $amt .'" />';
			$form .= '</form>';
			$form .= '<script type="text/javascript" language="javascript">';
			$form .= 'var form = document.getElementById("online");';
			$form .= 'form.submit();';
			$form .= '</script>';
			echo $form;
			insert($od_sob, $discuz_user, $discuz_uid, $amount, $price, $email, $ip);
			break;
	}
}
$res_str .= '</FONT></div></center>';
echo $res_str;
function err_set($id, $chk) {
	$err = FALSE;
	if('' == $id || '' == $chk) {
		$err = TRUE;
	}
	return $err;
}
function insert($od_sob, $discuz_user, $discuz_uid, $amount, $price, $email, $ip) {
	$data = array(
		'orderid' => $od_sob,
		'status' => '1',
		'buyer' => $discuz_user,
		'admin' => 'admin',
		'uid' => $discuz_uid,
		'amount' => $amount,
		'price' => $price,
		'submitdate' => time(),
		'confirmdate' => '0',
		'email' => $email,
		'ip' => $ip
	);
	C::t('forum_order')->insert($data);
}
?>