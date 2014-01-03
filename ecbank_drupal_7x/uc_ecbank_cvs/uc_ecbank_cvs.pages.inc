<?php

/**
 * @file
 * ecbank_cvs menu items.
 *
 */
 //print_r($_REQUEST);         //exit;

function uc_ecbank_cvs_complete($cart_id = 0) {
  watchdog('ecbank_cvs', 'Receiving new order notification for order !order_id.', array('!order_id' => check_plain($_POST['od_sob'])));

  $order = uc_order_load($_POST['od_sob']);  

	//簡單的檢查是否有問題,至此,訂單尚未產生在後台
  if ($order === FALSE || uc_order_status_data($order->order_status, 'state') != 'post_checkout') {
    print t('An error has occurred during payment.  Please contact us to ensure your order has submitted.');
    exit();
  }  
  
  //加入綠界的檢查機制
  $checkcode=variable_get('uc_ecbank_cvs_secret_word','');  
  $incom_check=gwSpcheck($_POST['proc_date'],$_POST['proc_time'],$_POST['tsr'],$_POST['tac'],$checkcode);	
  echo $incom_check ;
  //檢查授權成功與否,還有金額
  if ($_POST['succ'] == '1' && is_numeric($_POST['amt']) && $incom_check=='true' && $order->order_total==$_POST['amt']) {
  	
     $comment = t('付款成功,以綠界 ECBank 超商代碼方式繳費(ecbank_cvs), UberCart訂單編號#!order.', array('!type' => $_POST['pay_method'] == 'CC' ? t('cvs') : t('cvs'), '!order' => check_plain($_POST['od_sob'])));    
     uc_payment_enter($order->order_id, 'ecbank_cvs', $_POST['amt'], 0, NULL, $comment);    
     
     $order->order_status = 'payment_received';  
    // uc_order_update_status($order->order_id, uc_order_state_default('payment_received'));
     uc_order_update_status($order->order_id, 'payment_received');
     uc_order_comment_save($order->order_id, 0, t('付款成功,以綠界 ECBank 超商代碼方式繳費(ecbank_cvs),綠界交易單號='.$_POST['tsr'].''), 'admin');
  }else{  	  
  	 uc_order_comment_save($order->order_id, 0, t('綠界 ECBank 超商代碼繳費檢查授權失敗(ecbank_cvs)', array('!type' => $_POST['pay_method'] == 'CC' ? t('cvs') : t('cvs'))), 'admin');
  }
  
  print "OK";
  exit();   
}

function gwSpcheck($proc_date,$proc_time,$tsr,$tac,$checkcode) {		
		$ecbank_gateway='https://ecbank.com.tw/web_service/get_outmac_valid.php';
		// 組合字串
		$serial = trim($proc_date.$proc_time.$tsr);
		//echo "<hr>".$checkcode."<hr>";

		$post_parm	=	'key='.$checkcode.'&serial='.$serial.'&tac='.$tac;
		
		//echo "<hr>".$post_parm; 


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post_parm);
		$strAuth = curl_exec($ch);
		if (curl_errno($ch)) $strAuth = false;
		curl_close($ch);
	  //echo $strAuth; exit;
	
		if($strAuth == 'valid=1') {
        	return 'true';
    	}  else {
    		return 'fail';
    	}  
}