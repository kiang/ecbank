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

  class ecbank_alipay {
    var $code, $title, $description, $enabled;

// class constructor
    function ecbank_alipay() {
      global $order;

      $this->code = 'ecbank_alipay';
      $this->title = MODULE_PAYMENT_ECBANK_ALIPAY_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_ECBANK_ALIPAY_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_ECBANK_ALIPAY_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_ECBANK_ALIPAY_STATUS == 'True') ? true : false);
      
      if ((int)MODULE_PAYMENT_ECBANK_ALIPAY_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_ECBANK_ALIPAY_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
	
      //$this->form_action_url = 'https://ecbank.com.tw/gateway.php';	//中文頁面 Chinese  PayPage
      //$this->form_action_url = 'https://ecpay.com.tw/form_Sc_to5e.php';	//英文頁面 English      Paypage
      
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ECBANK_ALIPAY_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ECBANK_ALIPAY_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
		global $order;
		$ecbank_gateway = 'https://ecbank.com.tw/web_service/alipay_goods_upload.php';
		$type = 'upload_goods';
		$c_key = MODULE_PAYMENT_ECBANK_ALIPAY_CHECKCODE;
		$c_mid = MODULE_PAYMENT_ECBANK_ALIPAY_ID;
		$goods_href = 'http://';
		$goods = $order->products;
		//print_r($goods);
		
		foreach($goods as $good){ //先上架商品
			$post_str='enc_key='.$c_key.
					  '&mer_id='.$c_mid.
                      '&type='.$type.
                      '&goods_id='.$good['id'].
                      '&goods_title='.$good['name'].
                      '&goods_price='.round($good['price']).
                      '&goods_href='.$goods_href;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
                    curl_setopt($ch, CURLOPT_VERBOSE, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
                    $strAuth = curl_exec($ch);
                    if (curl_errno($ch)){
                        $strAuth = false;
                    }
                    curl_close($ch);
                    /*
                    if($strAuth == 'state=NEW_SUCCESS'){
                        echo '新增上架商品成功<br>';
                    }else if($strAuth == 'state=MODIFY_SUCCESS'){
                        echo '修改上架商品成功<br>';
                    }else{
                        echo '錯誤：'.$strAuth.'<br>';
                    }*/
					}
       return array('title' => MODULE_PAYMENT_ECBANK_ALIPAY_TEXT_CONFIRMATION);
    }

    function process_button() {
      global $order ,$mid ;
      $price = $order->info['total'];
	  if($price <=10) {
			echo "<Script Language=Javascript>
					alert('歐付寶 ECBank 支付寶 alipay付款, 最低付款金額為 10 元,按下確定後會返回購物車');
					location(history(-1));
				  </script>";
				  exit;
		}
$goods = $order->products;
	    $price = $order->info['total'];
		$orderid_sql =tep_db_query("select orders_id from " . TABLE_ORDERS . " ORDER BY orders_id DESC LIMIT 1" );
		$orderid_array = tep_db_fetch_array($orderid_sql);
		$ordnum = $orderid_array['orders_id']+1; //date('Ymdhis')
		$_SESSION['comments'] .= " ECBank 自訂訂單編號:".$ordnum."";
		//if ( MODULE_PAYMENT_PAYONLIE_RETUREURL != '') {$reurl = 'returl' ; $returl_var= MODULE_PAYMENT_ECBANK_ALIPAY_RETUREURL ; }  
		$roturl=tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
		$okurl=tep_href_link('gwresponse.php', '', 'SSL');
		$street_address = $order->customer['state'] . $order->customer['city'] .  $order->customer['street_address'] ;
		
		$process_button_string = tep_draw_hidden_field('mer_id', MODULE_PAYMENT_ECBANK_ALIPAY_ID ) .	
      			       			 tep_draw_hidden_field('payment_type','alipay') .
                               	 tep_draw_hidden_field('amt', $price) .
                               	 tep_draw_hidden_field('od_sob',$ordnum).
                               	 //tep_draw_hidden_field('roturl' , $returl_var ) .
								 tep_draw_hidden_field('return_url', $roturl) .						//觸發訂單狀態
								 tep_draw_hidden_field('ok_url', $okurl) .
                               	 //tep_draw_hidden_field('ordnum', $ordnum ) .                            
                               	 tep_draw_hidden_field('Pur_Name', $order->customer['firstname']) .           
                               	 tep_draw_hidden_field('Tel_Number', $order->customer['telephone']) .            
                               	 tep_draw_hidden_field('ADDress', $street_address) .   
                               	 tep_draw_hidden_field('Email', $order->customer['email_address']).
			       				 tep_draw_hidden_field('Mobile_Number',$Mobile_Number).
			       				 tep_draw_hidden_field('OSType',OSC);
			       				 foreach($goods as $good){
									$process_button_string .= tep_draw_hidden_field('goods_name[]', $good['id'] ) .	
															  tep_draw_hidden_field('goods_amount[]', $good['qty'] ) ;
								 }	
			       										// tep_draw_hidden_field('LG',UTF8) .
																//tep_draw_hidden_field('You_ID', $customer_id ) . 
																//tep_draw_hidden_field('Roturl' , $returl_var ) . 
																//tep_draw_hidden_field('Invoice_Name',$Invoice_Name).
                               	//tep_draw_hidden_field('Invoice_Num',$Invoice_Num).			
      return $process_button_string;
    }


    function before_process() {  
    	return true;
 	}

    function get_error() {
	  	switch ($_GET['error']) {
		  		case 1 :
		  				$error_message = MODULE_PAYMENT_ECBANK_ALIPAY_TEXT_ERROR_1 ;
		  				break ;
		 		 case 2 :
		  				$error_message = MODULE_PAYMENT_ECBANK_ALIPAY_TEXT_ERROR_2 ;
		  				break ;
		  		case 3 :
		  				$error_message = MODULE_PAYMENT_ECBANK_ALIPAY_TEXT_ERROR_3 ;
		 					 break ;
	  	}
      $error = array('title' => MODULE_PAYMENT_ECBANK_ALIPAY_TEXT_ERROR,
                     'error' => $error_message );
      return $error;
    }
	function response() {		//非預設
		global $order, $SID;	

		$od_sob = $_REQUEST['od_sob'];
		$checkcode = '';
		$checkcode_sql =  tep_db_query( "select configuration_value checkcode from configuration where configuration_key = 'MODULE_PAYMENT_ECBANK_ALIPAY_CHECKCODE'");
		$checkcode_array = tep_db_fetch_array($checkcode_sql);
		$checkcode = $checkcode_array['checkcode'];
		// 回傳的交易驗證壓碼 mac版
		$mac = trim($_REQUEST['mac']);
		
		$orders_status = '2'; //改變的訂單狀態依商家實際情況做修正
		/*
		$orders_status_sql = tep_db_query( "select configuration_value orders_status from configuration where configuration_key = 'MODULE_PAYMENT_ECBANK_ALIPAY_ORDER_STATUS_ID'");
		$orders_status_array = tep_db_fetch_array($orders_status_sql);
		$orders_status = $orders_status_array['orders_status'];
		*/
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr'].$_REQUEST['od_sob'].$_REQUEST['amt']);
		$ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid_new.php';
		// 串接驗證參數
		$post_str='key='.$checkcode.	'&serial='.$serial.  '&mac='.$mac;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
		$strAuth = curl_exec($ch);
		
		if (curl_errno($ch)){
			$strAuth = false;
		}
		
		$amt_sql =tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '".$od_sob."' and class = 'ot_total'" ); //增加amt判斷
		$amt_array = tep_db_fetch_array($amt_sql);
		
		if($strAuth == 'valid=1') {	
			if($_REQUEST['succ']=='1' && round($amt_array['value']) == $_REQUEST['amt']) { 
				echo "OK";
				$result_msg = '交易成功，Ecbank 交易單號：'.$_REQUEST['tsr'].'，處理日期時間'.$_REQUEST['proc_date'].$_REQUEST['proc_time'];
				tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) 
				values ('" . (int)$od_sob . "', '" .$orders_status . "', now(), '', '".$result_msg."')");
				tep_db_query("update " . TABLE_ORDERS . " set cc_type = '" . $_REQUEST['tsr'] . "',orders_status='".$orders_status."',last_modified = now()
					where orders_id = '" . $od_sob . "'");        
			}else {
				echo "Failure";
				$result_msg = "交易失敗";
				//tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) 
				//values ('" . (int)$od_sob . "', 1, now(), '', '".$result_msg."')");
			}   
		}else{
			echo "Illegal";		
		}
		//return true;
	}

    function after_process() {
		global $order,$cart;
		$orderid_sql =tep_db_query("select orders_id from " . TABLE_ORDERS . " ORDER BY orders_id DESC LIMIT 1" );
		$orderid_array = tep_db_fetch_array($orderid_sql);
		
		$od_sob = $orderid_array['orders_id']; //date('Ymdhis')
		$amt = $order->info['total'];
		$roturl=tep_href_link('index.php', '', 'SSL');
		$okurl=tep_href_link('gwresponse.php', '', 'SSL');
		$goods = $order->products;
		$param_str = '';
		$param_str = 'mer_id='.MODULE_PAYMENT_ECBANK_ALIPAY_ID.'&amt='.$amt.'&od_sob='.$od_sob.'&return_url='.$roturl.'&ok_url='.$okurl;
		foreach($goods as $good){
			$param_str .= '&goods_name[]='.$good['id'].'&goods_amount[]='.$good['qty'];
		}
		
		$cart->reset(true);
		// unregister session variables used during checkout
		tep_session_unregister('sendto');
		tep_session_unregister('billto');
		tep_session_unregister('shipping');
		tep_session_unregister('payment');
		tep_session_unregister('comments');		
		
		tep_redirect(tep_href_link('alipay_go.php',		$param_str		,'SSL'));
		
	  return true;
    }

    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ECBANK_ALIPAY_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('歐付寶ECbank 支付寶alipay 付款 結帳的顯示順序',	 'MODULE_PAYMENT_ECBANK_ALIPAY_SORT_ORDER', 		 '0', 							'歐付寶 ECBank 支付寶alipay 付款結帳的顯示順序，數字小者在前', 					 	'6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('歐付寶ECBank 支付寶alipay 付款 結帳前預設定單狀態', 'MODULE_PAYMENT_ECBANK_ALIPAY_ORDER_STATUS_ID', '0', 							'設定使用歐付寶 ECBank 支付寶alipay 付款卡 結帳時，預設的訂單狀態',	'6', '1', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('歐付寶ECBank 支付寶alipay 付款 結帳地區', 				 'MODULE_PAYMENT_ECBANK_ALIPAY_ZONE', 					 '0', 							'如果選擇地區，則只有該地區可以使用這個結帳方式', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) 							 values ('啟動 歐付寶ECBank 支付寶alipay 付款模組', 			 'MODULE_PAYMENT_ECBANK_ALIPAY_STATUS', 				 'True', 						'接受 <a target=_BLANK href=http://www.ecbank.com.tw><font color=green>歐付寶ECBank 支付寶alipay付款</font></a> 結帳？', 											'6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('歐付寶ECBank 支付寶alipay 付商家編號', 						 'MODULE_PAYMENT_ECBANK_ALIPAY_ID', 						 歐付寶商店代號', 		'請輸入歐付寶 ECBank 支付寶alipay 付款 商家編號', 											'6', '4', now())");
      //tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('歐付寶 ATM 付款 完成回傳網址', 		 'MODULE_PAYMENT_ECBANK_ALIPAY_RETUREURL', 		 	 'http://OSC網站網址/checkout_process.php', 					'請輸入完成刷卡後將要回傳的網址.', 								'6', '5', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('回傳查核設定', 									 'MODULE_PAYMENT_ECBANK_ALIPAY_CHECKCODE',			 '商家檢查碼','填歐付寶 ECbank 支付寶alipay付款的商家檢查碼,可避免偽冒', 							'6', '6', now())");
           
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      //return array('MODULE_PAYMENT_ECBANK_ALIPAY_STATUS', 'MODULE_PAYMENT_ECBANK_ALIPAY_ID', 'MODULE_PAYMENT_ECBANK_ALIPAY_RETUREURL', 'MODULE_PAYMENT_ECBANK_ALIPAY_ZONE', 'MODULE_PAYMENT_ECBANK_ALIPAY_ORDER_STATUS_ID', 'MODULE_PAYMENT_ECBANK_ALIPAY_SORT_ORDER','MODULE_PAYMENT_ECBANK_ALIPAY_CHECKCODE');
	  return array('MODULE_PAYMENT_ECBANK_ALIPAY_STATUS', 'MODULE_PAYMENT_ECBANK_ALIPAY_ID', 'MODULE_PAYMENT_ECBANK_ALIPAY_ZONE', 'MODULE_PAYMENT_ECBANK_ALIPAY_ORDER_STATUS_ID', 'MODULE_PAYMENT_ECBANK_ALIPAY_SORT_ORDER','MODULE_PAYMENT_ECBANK_ALIPAY_CHECKCODE');
    }
  }
?>
