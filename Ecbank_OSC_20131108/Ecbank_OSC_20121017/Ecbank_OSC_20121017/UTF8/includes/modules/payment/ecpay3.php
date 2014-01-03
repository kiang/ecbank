<?php
/*
GreenWorld http://www.greenworld.com.tw
Date:2005/12/28
Version:2.22
*/



/*
  $Id: payonline.php,v 1.0.0.1 2003/07/03 12:00:03 odie Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2002 osCommerce
  Released under the GNU General Public License

  Traditional Chinese language pack(Big5 code) for osCommerce 2.2 ms1
  Community: http://www.dcezgo.com 
  Author(s): Odie Chiou (odie@dcezgo.com)
  Released under the GNU General Public License ,too!!

*/

  class ecpay3 {
    var $code, $title, $description, $enabled;

// class constructor
    function ecpay3() {
      global $order;

      $this->code = 'ecpay3';
      $this->title = MODULE_PAYMENT_ECPAY3_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_ECPAY3_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_ECPAY3_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_ECPAY3_STATUS == 'True') ? true : false);
      
      if ((int)MODULE_PAYMENT_ECPAY3_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_ECPAY3_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
	
      $this->form_action_url = 'https://ecpay.com.tw/form_Sc_to5_fn.php';	//中文頁面 Chinese  PayPage
      //$this->form_action_url = 'https://ecpay.com.tw/form_Sc_to5e.php';	//英文頁面 English      Paypage
      
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ECPAY3_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ECPAY3_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
       return array('title' => MODULE_PAYMENT_ECPAY3_TEXT_CONFIRMATION);
    }

    function process_button() {
      global $order ,$mid ;
	  $orderid_sql =tep_db_query("select orders_id from " . TABLE_ORDERS . " ORDER BY orders_id DESC LIMIT 1" );
	  $orderid_array = tep_db_fetch_array($orderid_sql);
	  
      $price = $order->info['total'];
      $ordnum = $orderid_array['orders_id']+1; //date('Ymdhis')
      $_SESSION['comments'] .= " ECpay 自訂訂單編號:".$ordnum."";
      // if ( MODULE_PAYMENT_PAYONLIE_RETUREURL != '') {$reurl = 'returl' ; $returl_var= MODULE_PAYMENT_ECPAY_RETUREURL ; }  
	  $roturl=tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
	  $street_address = $order->customer['state'] . $order->customer['city'] .  $order->customer['street_address'] ;
      $process_button_string = 	tep_draw_hidden_field('client', MODULE_PAYMENT_ECPAY3_ID ) .	
      			       						tep_draw_hidden_field('act',auth) .                    
                               				tep_draw_hidden_field('amount', $price) .
                               				tep_draw_hidden_field('od_sob',$ordnum).
                               				//tep_draw_hidden_field('roturl' , $returl_var ) .
											tep_draw_hidden_field('roturl' , $roturl ) .
                               				tep_draw_hidden_field('ordnum', $ordnum ) . 
											tep_draw_hidden_field('stage', 3 ) . 											
                               				tep_draw_hidden_field('Pur_Name', $order->customer['firstname']) .           
                               				tep_draw_hidden_field('Tel_Number', $order->customer['telephone']) .            
                               				tep_draw_hidden_field('ADDress', $street_address) .   
                               				tep_draw_hidden_field('Email', $order->customer['email_address']).
			       							tep_draw_hidden_field('Mobile_Number',$Mobile_Number).
			       							tep_draw_hidden_field('OSType',OSC);
			       										
			       										// tep_draw_hidden_field('LG',UTF8) .
																//tep_draw_hidden_field('You_ID', $customer_id ) . 
																//tep_draw_hidden_field('Roturl' , $returl_var ) . 
																//tep_draw_hidden_field('Invoice_Name',$Invoice_Name).
                               	//tep_draw_hidden_field('Invoice_Num',$Invoice_Num).
																
      return $process_button_string;
    }


    function before_process() {  

    //$loginName=MODULE_PAYMENT_ECPAY_CHECKCODE;
		$checkcode_sql =  tep_db_query( "select configuration_value checkcode from configuration where configuration_key = 'MODULE_PAYMENT_ECPAY3_CHECKCODE'");
		$checkcode_array = tep_db_fetch_array($checkcode_sql);
		$checkcode = $checkcode_array['checkcode'];
	$od_sob = $_POST['od_sob'];
	$amount = $_POST['amount'];
	$TOkSi = $_POST['process_time'] + $_POST['gwsr'] + $_POST['amount'];
	$orders_status = '2';//此訂單狀態可根據使用者的需求而設定
	
	$a = substr($TOkSi,0,1).substr($TOkSi,2,1).substr($TOkSi,4,1);
	$b = substr($TOkSi,1,1).substr($TOkSi,3,1).substr($TOkSi,5,1);
	$my_spcheck = ( $checkcode % $TOkSi ) + $checkcode + $a + $b;
	
	//$my_spcheck = gwSpcheck($checkcode,$TOkSi);
	if($my_spcheck == $_POST['spcheck']) {
    			if($_POST['succ']==1) {
					$_SESSION['PAY_SUCC'] = 'GREENWORLD';
					$_SESSION['gwsr'] = $_REQUEST['gwsr'];
					$_SESSION['od_sob'] = $od_sob;
					$_SESSION['orders_status'] = $orders_status;
					
					$result_msg = '交易成功，Ecpay 交易單號：'.$_REQUEST['gwsr'].'，處理日期時間'.$_REQUEST['process_date'].$_REQUEST['process_time'];
					tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) 
						values ('" . (int)$od_sob . "', '" .$orders_status . "', now(), '', '".$result_msg."')");      
						return true;
						} else {
							$error = 2;
							$payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) ;
							tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
							
						}
		} else {
		$error = 3;	
        $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) ;
			tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));	
			
		}
 	
    return true;
 }

    
	function get_error() {
	  	switch ($_GET['error']) {
		  		case 1 :
		  				$error_message = MODULE_PAYMENT_ECPAY3_TEXT_ERROR_1 ;
		  				break ;
		 		 case 2 :
		  				$error_message = MODULE_PAYMENT_ECPAY3_TEXT_ERROR_2 ;
		  				break ;
		  		case 3 :
		  				$error_message = MODULE_PAYMENT_ECPAY3_TEXT_ERROR_3 ;
		 					 break ;
	  	}
      $error = array('title' => MODULE_PAYMENT_ECPAY3_TEXT_ERROR,
                     'error' => $error_message );
      return $error;
    }
	
    function after_process() {
      if($_SESSION['PAY_SUCC'] == 'GREENWORLD'){
		tep_db_query("update " . TABLE_ORDERS . " set cc_type = '" . $_SESSION['gwsr'] . "',orders_status='".$_SESSION['orders_status']."',last_modified = now()
			where orders_id = '" . $_SESSION['od_sob'] . "'"); 
	  }
	  unset($_SESSION['PAY_SUCC']) ;
	  unset($_SESSION['gwsr']) ;
	  unset($_SESSION['orders_status']) ;
	  unset($_SESSION['od_sob']) ;
	  return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ECPAY3_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
    	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('歐付寶ECPAY刷卡 分期付款-3期 結帳的顯示順序',	 'MODULE_PAYMENT_ECPAY3_SORT_ORDER', 		 '0', 							'ECPAY 刷卡 分期付款-3期，結帳的顯示順序，數字小者在前', 					 	'6', '0', now())");
    	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('歐付寶ECPAY刷卡 分期付款-3期 結帳預設定單狀態', 'MODULE_PAYMENT_ECPAY3_ORDER_STATUS_ID', '0', 							'設定使用歐付寶ECPAY刷卡 分期付款-3期 結帳時，預設的訂單狀態',	'6', '1', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('歐付寶ECPAY刷卡 分期付款-3期 結帳地區', 				 'MODULE_PAYMENT_ECPAY3_ZONE', 					 '0', 							'如果選擇地區，則只有該地區可以使用這個結帳方式', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) 							 values ('啟動 歐付寶ECPAY 分期付款-3期 刷卡模組', 			 'MODULE_PAYMENT_ECPAY3_STATUS', 				 'True', 						'接受 <a href=http://www.gwpay.com.tw target=_BLANK><font color=green>歐付寶ECPAY刷卡 分期付款-3期</font></a> 結帳？', 											'6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('歐付寶ECPAY 分期付款商家編號', 						 'MODULE_PAYMENT_ECPAY3_ID', 						 '歐付寶商店代號', 		'請輸入 歐付寶ECPAY 分期付款商家編號', 											'6', '4', now())");
      //tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('歐付寶ECPAY刷卡 完成回傳網址', 		 'MODULE_PAYMENT_ECPAY_RETUREURL', 		 	 'http://OSC網站網址/checkout_process.php', 					'請輸入完成刷卡後將要回傳的網址.', 								'6', '5', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('回傳查核設定', 									 'MODULE_PAYMENT_ECPAY3_CHECKCODE',			 '交易驗證碼','填上歐付寶金流的管理帳號,可避免偽冒', 							'6', '6', now())");
           
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_ECPAY3_STATUS', 'MODULE_PAYMENT_ECPAY3_ID', 'MODULE_PAYMENT_ECPAY3_ZONE', 'MODULE_PAYMENT_ECPAY3_ORDER_STATUS_ID', 'MODULE_PAYMENT_ECPAY3_SORT_ORDER','MODULE_PAYMENT_ECPAY3_CHECKCODE');
    }
  }
?>
