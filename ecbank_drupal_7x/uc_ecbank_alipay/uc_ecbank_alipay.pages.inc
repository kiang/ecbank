<?php

/**
 * @file
 * ecbank_alipay menu items.
 *
 */
 //print_r($_REQUEST);         

function uc_ecbank_alipay_complete($cart_id = 0) {
  watchdog('ecbank_alipay', 'Receiving new order notification for order !order_id.', array('!order_id' => check_plain($_POST['od_sob'])));
//echo $_POST['od_sob'];
  $order = uc_order_load($_POST['od_sob']);
//echo "<hr>";
//print_r($order); 
//echo "<hr>";
//echo uc_order_status_data($order->order_status, 'state');
//exit;
    
	//簡單的檢查是否有問題,至此,訂單尚未產生在後台
      
  if ($order === FALSE || uc_order_status_data($order->order_status, 'state') != 'in_checkout') {
    print t('An error has occurred during payment.  Please contact us to ensure your order has submitted.');
    exit();
  }

/*	
  // 綠界沒有 Demo 這樣的狀態可以設置
  if ($_POST['demo'] != 'Y') {
    $key = $_POST['key'];
    $valid = md5(variable_get('uc_greenworld_secret_word', 'tango') . $_POST['mer_id'] . $_POST['od_sob'] . $_POST['amt']);
    if (strtolower($key) != strtolower($valid)) {
      uc_order_comment_save($order->order_id, 0, t('Attempted unverified ecbank_alipay completion for this order.'), 'admin');
    }
  }

  //需要帳單地址,這一段綠界沒有這東西可以回傳
  $order->billing_street1 = $_POST['street_address'];
  $order->billing_street2 = $_POST['street_address2'];
  $order->city = $_POST['city'];		
  $order->billing_postal_code = $_POST['zip'];
  $order->billing_phone = $_POST['phone'];

  //洲的參數 ... 在台灣沒有洲這個問題, 但國外有,所以理論上還是得拆解出 state 出來
  $zone_id = db_query("SELECT zone_id FROM {uc_zones} WHERE zone_code LIKE :code", array(':code' => $_POST['state']))->fetchField();
  if (!empty($zone_id)) {
    $order->billing_zone = $zone_id;
  }

  //國家的值
  $country_id = db_query("SELECT country_id FROM {uc_countries} WHERE country_name LIKE :name", array(':name' => $_POST['country']))->fetchField();
  if (!empty($country_id)) {
    $order->billing_country = $country_id;
  }

   //檢查回傳的 email address 是否是這個購物網站內所登記的會員 email 帳號
  if (strtolower($_POST['email']) !== strtolower($order->primary_email)) {
    uc_order_comment_save($order->order_id, 0, t('Customer used a different e-mail address during payment: !email', array('!email' => check_plain($_POST['email']))), 'admin');
  }
*/

   	//加入綠界的檢查機制
  	$checkcode=variable_get('uc_ecbank_alipay_secret_word','');
	$incom_check=gwSpcheck($_POST['proc_date'],$_POST['proc_time'],$_POST['tsr'],$_POST['mac'],$checkcode,$_POST['od_sob'],$_POST['amt']);

	  //echo $incom_check; //exit;

  // print_r($_REQUEST);
  //檢查授權成功與否,還有金額
  if ($_POST['succ'] == '1' && is_numeric($_POST['amt']) && $incom_check=='true' && $order->order_total==$_POST['amt']) {
    $comment = t('付款成功,以綠界 ECBank alipay方式繳費(ecbank_alipay), UberCart 訂單編號#!order.', array('!type' => t('alipay'), '!order' => check_plain($_POST['od_sob'])));
    uc_payment_enter($order->order_id, 'ecbank_alipay', $_POST['amt'], 0, NULL, $comment);

    // Empty that cart...
    uc_cart_empty($cart_id);
  
    //$order->order_status = 'payment_received';
    //uc_order_update_status($order->order_id, uc_order_state_default('payment_received'));
    uc_order_update_status($order->order_id, 'Payment received' );     
      
    uc_order_comment_save($order->order_id, 0, t('付款成功,以綠界ECBank alipay方式繳費(ecbank_alipay),綠界交易單號='.$_POST['tsr'].''), 'admin');
      
    $url = 'cart/ecbank_alipay/finalize/'. $order->order_id;
      
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
  else {  	 
	    uc_order_comment_save($order->order_id, 0, t('以綠界ECBank alipay方式繳費付款失敗(ecbank_alipay)', array('!type' => $_POST['pay_method'] == 'CC' ? t('alipay') : t('alipay'))), 'admin');
	     
        drupal_set_message(t('付款失敗，請重新付款！'), 'error');		 
	    drupal_goto('cart/checkout');
		     
  }
  echo "OK";
  exit();
}

function uc_ecbank_alipay_finalize() {
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

function gwSpcheck($proc_date,$proc_time,$tsr,$mac,$checkcode,$od_sob,$amt) {    
		
		$ecbank_gateway='https://ecbank.com.tw/web_service/get_outmac_valid_new.php';
		// 組合字串
		$serial = trim($proc_date.$proc_time.$tsr.$od_sob.$amt);
		//echo "<hr>".$checkcode."<hr>";

		$post_parm	=	'key='.$checkcode.'&serial='.$serial.'&mac='.$mac;
		
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