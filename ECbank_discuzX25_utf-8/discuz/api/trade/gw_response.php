<?php
require '../../source/class/class_core.php';
$discuz = C::app();
$discuz->init();
$arr = C::t('common_plugin')->fetch_by_identifier('gw');
$pluginid = $arr['pluginid'];
foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($pluginid) as $plugin) {
	$vars[$plugin['variable']] = $plugin['value'];
}
$ecbank_key = $vars['ecbank_key'];
$payment_type = $_POST['payment_type'];
switch($payment_type) {
	case 'alipay' :
	case 'tenpay' :
	    $serial = trim($_POST['proc_date'].$_POST['proc_time'].$_POST['tsr'].$_POST['od_sob'].$_POST['amt']);
		$ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid_new.php';
		$post_parm = 'key=' . $ecbank_key . '&serial='.$serial . '&mac=' . $mac;
		break;
	case 'ibon' :
	case 'cvs' :
	case 'vacc' :
	case 'web_atm' :
	case 'paypal' :
	case 'barcode' :
		$serial = trim($_POST['proc_date'].$_POST['proc_time'].$_POST['tsr']);
		$tac = trim($_POST['tac']);
		$ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';
		$post_parm	=	'key=' . $ecbank_key . '&serial=' . $serial . '&tac='.$tac;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $ecbank_gateway);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $post_parm);
		$strAuth = curl_exec($ch);
		if (curl_errno($ch)){
			$strAuth = false;
		}
		curl_close($ch);
		if($strAuth == 'valid=1') {
			if($_POST['succ']=='1') {
				echo "OK";
				insert($_POST['od_sob']);
				switch($payment_type){
				case 'paypal':
				case 'web_atm':
					$home = strrpos($_SERVER['REQUEST_URI'],'/api');
					$cur_url = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, $home);
					header("Location:".$cur_url);
					exit;
					break;
				default:
					break;
				}
			}
		}
		break;
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