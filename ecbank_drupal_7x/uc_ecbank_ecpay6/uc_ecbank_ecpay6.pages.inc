<?php

/**
 * @file
 * ecbank_ecpay6 menu items.
 *
 */
 //print_r($_REQUEST);         

function uc_ecbank_ecpay6_complete($cart_id = 0) {

  watchdog('ecbank_ecpay6', 'Receiving new order notification for order !order_id.', array('!order_id' => check_plain($_POST['od_sob'])));

  $order = uc_order_load($_POST['od_sob']);
//echo "<hr>";
//print_r($order); 
//echo "<hr>";
//echo uc_order_status_data($order->order_status, 'state');
//echo $order;
// exit;
     
	//簡單的檢查是否有問題,至此,訂單尚未產生在後台

	
 // if ($order === FALSE || uc_order_status_data($order->order_status, 'state') != 'post_checkout') {
   // print t('An error has occurred during payment.  Please contact us to ensure your order has submitted.');
   // exit();
 // }



   	//加入綠界的檢查機制
  	$checkcode=variable_get('uc_ecbank_ecpay6_secret_word','');
	$incom_check=gwSpcheck($_POST['process_date'],$_POST['process_time'],$_POST['gwsr'],$_POST['rech_key'],$_POST['amount']);

	

  // print_r($_REQUEST);
  //檢查授權成功與否,還有金額

  if ($_POST['succ'] == '1'  && $incom_check=='true') {
      $comment = t('付款成功,以綠界 ECBank ecpay6方式繳費(ecbank_ecpay6), UberCart 訂單編號#!order.', array('!order' => check_plain($_POST['od_sob'])));
	  uc_payment_enter($order->order_id, 'ecbank_ecpay6', $_POST['amount'], 0, NULL, $comment);
	  // Empty that cart...
      uc_cart_empty($cart_id);
	  $order->order_status = 'payment_received';
   
      //uc_order_update_status($order->order_id, uc_order_state_default('payment_received'));
      uc_order_update_status($order->order_id, 'payment_received'); 	  
	  uc_order_comment_save($order->order_id, 0, t('付款成功,以綠界ECBank ecpay6方式繳費(ecbank_ecpay6),綠界交易單號='.$_POST['gwsr'].''), 'admin');
    
	
      $url = 'cart/ecbank_ecpay6/finalize/'. $order->order_id;
	  
	  // Javascript redirect on the finalization page.
      $build = '<script type="text/javascript">window.location = "'. url($url, array('absolute' => TRUE)) .'";</script>';
      
    // Text link for users without Javascript enabled.
      $build .= l(t('Click to complete checkout.'), $url, array('absolute' => TRUE));
      
    // 2CO needs page larger than 255 characters to display.
      while (strlen($build) < 255) {
           $build .= '&nbsp;';
      }
      print $build;
      
  }
  else{
        uc_order_comment_save($order->order_id, 0, t('以綠界ECBank ecpay6方式繳費付款失敗(ecbank_ecpay6)',array('admin')));
	    
        drupal_set_message(t('付款失敗，請重新付款！'), 'error');		 
	    drupal_goto('cart/checkout');
  }

  exit();
  
}

function uc_ecbank_ecpay6_finalize() {
  $order = uc_order_load(arg(3));

  // Add a comment to let sales team know this came in through the site.
  uc_order_comment_save($order->order_id, 0, t('Order created through website.'), 'admin');

  $build = uc_cart_complete_sale($order, variable_get('uc_new_customer_login', FALSE));

  $page = variable_get('uc_cart_checkout_complete_page', '');

  if (!empty($page)) {
    drupal_goto($page);
  }
  return $build;
}

function gwSpcheck($process_date,$process_time,$gwsr,$rech_key,$amount) {    
		
		$ecbank_gateway='https://ecpay.com.tw/g_recheck.php';
		// 組合字串
		$serial = trim($process_date.$process_time.$gwsr);
		//echo "<hr>".$checkcode."<hr>";

		$post_parm	=	'key='.$rech_key.'&serial='.$serial.'&amt='.$amount;
		
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