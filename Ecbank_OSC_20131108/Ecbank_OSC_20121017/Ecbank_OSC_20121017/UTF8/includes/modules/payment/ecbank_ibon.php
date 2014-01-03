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

  class ecbank_ibon {
    var $code, $title, $description, $enabled;

// class constructor
    function ecbank_ibon() {
      global $order;

      $this->code = 'ecbank_ibon';
      $this->title = MODULE_PAYMENT_ECBANK_IBON_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_ECBANK_IBON_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_ECBANK_IBON_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_ECBANK_IBON_STATUS == 'True') ? true : false);
      
      if ((int)MODULE_PAYMENT_ECBANK_IBON_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_ECBANK_IBON_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
	
      //$this->form_action_url = 'https://ecbank.com.tw/gateway.php';	//中文頁面 Chinese  PayPage
      //$this->form_action_url = 'https://ecpay.com.tw/form_Sc_to5e.php';	//英文頁面 English      Paypage
      
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ECBANK_IBON_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ECBANK_IBON_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
		global $order, $SID;	
		//print_r($order);
	   $orderid_sql =tep_db_query("select orders_id from " . TABLE_ORDERS . " ORDER BY orders_id DESC LIMIT 1" );
	   $orderid_array = tep_db_fetch_array($orderid_sql);
       $param = array (
		
 			// ecbank主機
 			'ecbank_gateway' =>  'https://ecbank.com.tw/gateway.php',
 			// 您的ECBank商店代號
 			'mer_id' => MODULE_PAYMENT_ECBANK_IBON_MID,
			'payment_type' => 'ibon',
 			// 商店設定在ECBank管理後台的交易加密私鑰
 			'enc_key' => MODULE_PAYMENT_ECBANK_IBON_CHECKCODE,
 			// 商品說明及備註。(會出現在超商繳費平台螢幕上)
 			'od_sob' => $orderid_array['orders_id']+1, //date('Ymdhis')
 			'prd_desc' => "歐付寶 7-11Ibon條碼繳費服務",
 			//'desc1' => rawurlencode('顏色：粉紅色'),
 			//'desc2' => rawurlencode('w30 h60'),
 			//'desc3' => rawurlencode('2010 限量款'),
 			//'desc4' => rawurlencode('付款完請保留繳費收据'),
 			// 繳費金額
 			'amt' => round($order->info['total']) ,
 			// 付款完成通知網址
			'ok_url' => tep_href_link("gwresponse.php", '', 'SSL')
		);

		$strAuth = '';
		$nvpStr = 	'payment_type='.$param['payment_type'].
   					'&od_sob='.$param['od_sob'].
   					'&mer_id='.$param['mer_id'].
   					'&enc_key='.$param['enc_key'].
   					'&amt='.$param['amt'].
   					'&prd_desc='.$param['prd_desc'].
 				    '&ok_url='.$param['ok_url'];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$param['ecbank_gateway']);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpStr);
			$strAuth = curl_exec($ch);
			if (curl_errno($ch)) $strAuth = false;
			curl_close($ch);
		
	if($strAuth) {
 			parse_str($strAuth, $res);
 		if(!isset($res['error']) || $res['error'] != '0') {
 	  		$text='取號錯誤:錯誤代碼 = ('.$res['error'].')';
		} else {	
		//$text="您所選用的是超商代碼支付 代碼為".$res['payno']."<br>";
		$_SESSION['comments'] .= " ECBank 自訂訂單編號:".$res['od_sob']." ECBank 交易單號(".$res['tsr'].") 繳費代碼(".$res['payno'].")";    
		$text='<table width=90% border=1 align=center cellpadding=3 cellspacing=1>
                 <tr>
                   <td width=149 align=center bgcolor=#FFFF99>付款方式</td>
                   <td width=235 bgcolor=#FFFF99>7-11 Ibon條碼付款 </td>
                 </tr>
                 <tr>
                   <td align=center>付款金額</td>
                   <td>'.intval($res['amt']).'元</td>
                 </tr>
                 <tr>
                   <td align=center>訂單編號</td>
                   <td>'.$res['od_sob'].'</td>
                 </tr>
                 <tr>
                   <td colspan=2><strong class=mycolor><a href=http://www.ecbank.com.tw/expenses-ibon.htm target=_blank>統一超商7-Eleven ibon 門市操作步驟</a></strong></td>
                 </tr>
				 <tr>
                   <td colspan=2>請記下上列超商繳費代碼,至最近之統一超商7-Eleven便利商店,操作代碼繳費機台, 於列印出有條碼之繳款單後,至櫃台支付,<br>便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程</td>
                 </tr>
                 <tr>
                   <td colspan=2 align=center class=myfont><br>
                     本線上金流機制採用&lt; <a href=http://www.ecbank.com.tw target=_blank>歐付寶 ECBank 線上支付平台</a> ,請安心使用
				     <hr>
				     <strong class=mycolor>要點選下方的"確認訂單"鈕才算完成訂單程序喔</strong>
				   </td>
				  </tr>
               </table>';
		
		
		}
	}
		return array('title' =>$text);
    }

    function process_button() {
      global $order ,$mid ;

      
	  $price = $order->info['total'];
     
	  if($price <=30) {
			echo "<Script Language=Javascript>
					alert('歐付寶 ECBank 7-11 IBON 付款, 最低付款金額為 30 元,按下確定後會返回購物車');
					location(history(-1));
				  </script>";
				  exit;
		} 
	  
      //$ordnum = date('Ymdhis');
      //$_SESSION['comments'] .= " ECBank 自訂訂單編號:".$ordnum."";      
		  $roturl=tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
		  $street_address = $order->customer['state'] . $order->customer['city'] .  $order->customer['street_address'] ;
		  $c_mid	=	MODULE_PAYMENT_ECBANK_IBON_MID;
		  $key	=	MODULE_PAYMENT_ECBANK_IBON_CHECKCODE;
     
	    $process_button_string = 	tep_draw_hidden_field('mer_id', MODULE_PAYMENT_ECBANK_IBON_MID ) .	
	                              tep_draw_hidden_field('amt', $price) ;

	
	    return $process_button_string;
    }


    function before_process() {  
		return true;
	}
	
    function after_process() {		
      return true;
    }
	function response() {		
		global $order, $SID;	
		
		$od_sob = $_REQUEST['od_sob'];
		$checkcode = '';
		$checkcode_sql =  tep_db_query( "select configuration_value checkcode from configuration where configuration_key = 'MODULE_PAYMENT_ECBANK_IBON_CHECKCODE'");
		$checkcode_array = tep_db_fetch_array($checkcode_sql);
		$checkcode = $checkcode_array['checkcode'];
		
		$orders_status = '2'; //改變的訂單狀態依商家實際情況做修正
		/*
		$orders_status_sql = tep_db_query( "select configuration_value orders_status from configuration where configuration_key = 'MODULE_PAYMENT_ECBANK_IBON_ORDER_STATUS_ID'");
		$orders_status_array = tep_db_fetch_array($orders_status_sql);
		$orders_status = $orders_status_array['orders_status'];
		*/
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
		$tac = trim($_REQUEST['tac']);
		$ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';
		$post_parm	=	'key='.$checkcode.'&serial='.$serial.'&tac='.$tac;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post_parm);
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


  function get_error() {
	  	switch ($_GET['error']) {
		  		case 1 :
		  				$error_message = MODULE_PAYMENT_ECBANK_IBON_TEXT_ERROR_1 ;
		  				break ;
		 		 case 2 :
		  				$error_message = MODULE_PAYMENT_ECBANK_IBON_TEXT_ERROR_2 ;
		  				break ;
		  		case 3 :
		  				$error_message = MODULE_PAYMENT_ECBANK_IBON_TEXT_ERROR_3 ;
		 					 break ;
	  	}
      $error = array('title' => MODULE_PAYMENT_ECBANK_IBON_TEXT_ERROR,
                     'error' => $error_message );
      return $error;
    }


    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ECBANK_IBON_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 			                 values ('歐付寶ECBank 7-11 IBON 付款 結帳的顯示順序',	'MODULE_PAYMENT_ECBANK_IBON_SORT_ORDER', 		 '0', 							'歐付寶 ECBank 7-11 IBON 付款結帳的顯示順序，數字小者在前', 					 	'6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('歐付寶ECBank 7-11 IBON 付款 結帳預設定單狀態',  'MODULE_PAYMENT_ECBANK_IBON_ORDER_STATUS_ID',    '0', 							'設定使用歐付寶 ECBank 7-11 IBON 付款卡 結帳時，預設的訂單狀態',	'6', '1', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('歐付寶ECBank 7-11 IBON 付款 結帳地區', 		    'MODULE_PAYMENT_ECBANK_IBON_ZONE', 				 '0', 							'如果選擇地區，則只有該地區可以使用這個結帳方式', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) 				 values ('啟動 歐付寶ECBank 7-11 IBON 付款模組', 		    'MODULE_PAYMENT_ECBANK_IBON_STATUS', 			 'True', 						'接受 <a target=_BLANK href=http://www.ecbank.com.tw><font color=green>歐付寶ECBank-7-11 IBON付款</font></a> 結帳？', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 							 values ('歐付寶ECBank 7-11 IBON 付款商家編號',    		'MODULE_PAYMENT_ECBANK_IBON_MID', 			     歐付寶商店代號', 				'請輸入歐付寶 ECBank 7-11 IBON 付款 商家編號', 											'6', '4', now())");
      //tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 														 values ('歐付寶 ATM 付款 完成回傳網址', 		 'MODULE_PAYMENT_ECBANK_IBON_RETUREURL', 		 	 'http://OSC網站網址/checkout_process.php', 					'請輸入完成刷卡後將要回傳的網址.', 								'6', '5', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) 							 values ('回傳查核設定', 	                            'MODULE_PAYMENT_ECBANK_IBON_CHECKCODE',	    '商家檢查碼','填歐付寶 ECbank 7-11 IBON 付款的商家檢查碼,可避免偽冒', 							'6', '6', now())");
           
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      //return array('MODULE_PAYMENT_ECBANK_IBON_STATUS', 'MODULE_PAYMENT_ECBANK_IBON_ID', 'MODULE_PAYMENT_ECBANK_IBON_RETUREURL', 'MODULE_PAYMENT_ECBANK_IBON_ZONE', 'MODULE_PAYMENT_ECBANK_IBON_ORDER_STATUS_ID', 'MODULE_PAYMENT_ECBANK_IBON_SORT_ORDER','MODULE_PAYMENT_ECBANK_IBON_CHECKCODE');
	  return array('MODULE_PAYMENT_ECBANK_IBON_STATUS', 'MODULE_PAYMENT_ECBANK_IBON_MID', 'MODULE_PAYMENT_ECBANK_IBON_ZONE', 'MODULE_PAYMENT_ECBANK_IBON_ORDER_STATUS_ID', 'MODULE_PAYMENT_ECBANK_IBON_SORT_ORDER','MODULE_PAYMENT_ECBANK_IBON_CHECKCODE');
    }
  }
?>
