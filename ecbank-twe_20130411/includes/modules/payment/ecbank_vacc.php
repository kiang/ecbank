<?php
/*

   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(german.php,v 1.119 2003/05/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (german.php,v 1.25 2003/08/25); www.nextcommerce.org
   (c) 2003	 xtcommerce  www.xt-commerce.com   
   Released under the GNU General Public License 
*/

  class ecbank_vacc {
    var $code, $title, $description, $enabled;

// class constructor
    function ecbank_vacc() {
      global $order;

      $this->code = 'ecbank_vacc';
      $this->title = MODULE_PAYMENT_ECBANK_VACC_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_ECBANK_VACC_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_ECBANK_VACC_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_ECBANK_VACC_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_ECBANK_VACC_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_ECBANK_VACC_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

	   $this->form_action_url = MODULE_PAYMENT_ECBANK_VACC_FORM_ACTION_URL;	

    }

	function payment_error( $url_payment_error ) {
      twe_redirect(twe_href_link(FILENAME_CHECKOUT_PAYMENT, $url_payment_error, 'SSL', true, false));
    }

// class methods
    function update_status() {
      global $order,$db;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ECBANK_VACC_ZONE > 0) ) {
        $check_flag = false;
        $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ECBANK_VACC_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while (!$check->EOF) {
          if ($check->fields['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
 		$check->MoveNext();  
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
      $selection = array('id' => $this->code,
                         'module' => $this->title);
      return $selection;
    }

    function pre_confirmation_check() {
		return false;
    }

    function confirmation() {
		global $order, $SID;
		$SID = twe_session_name() . '=' . twe_session_id();
		//取號開始
		$param = array (
 			// ecbank主機
 			'ecbank_gateway' =>  'https://ecbank.com.tw/gateway.php',
 			// 您的ECBank商店代號
 			'mer_id' => MODULE_PAYMENT_ECBANK_VACC_MID,
			'payment_type' => 'vacc',
			// 收單銀行私鑰
 			'setbank' => 'ESUN',
 			// 商店設定在ECBank管理後台的交易加密私鑰
 			'enc_key' => MODULE_PAYMENT_ECBANK_VACC_CHECKCODE,
 			// 商品說明及備註。(會出現在超商繳費平台螢幕上)
 			'od_sob' => date('Ymdhis'),
 			//'prd_desc' => "123",
 			//'desc1' => rawurlencode('顏色：粉紅色'),
 			//'desc2' => rawurlencode('w30 h60'),
 			//'desc3' => rawurlencode('2010 限量款'),
 			//'desc4' => rawurlencode('付款完請保留繳費收据'),
 			// 繳費金額
			'expire_day' => '7',
 			'amt' => round($order->info['total']) ,
 			// 付款完成通知網址
 			'ok_url' => twe_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')
		);

		$strAuth = '';
		$nvpStr = 	'payment_type='.$param['payment_type'].
                    '&setbank='.$param['setbank'].
   					'&od_sob='.$param['od_sob'].
   					'&mer_id='.$param['mer_id'].
   					'&enc_key='.$param['enc_key'].
   					'&amt='.$param['amt'].
   					'&prd_desc='.$param['prd_desc'].
 					'&expire_day='.$param['expire_day'].
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
		$text='<table width=90% border=1 align=center cellpadding=3 cellspacing=1>
                 <tr>
                   <td width=149 align=center bgcolor=#FFFF99>付款方式</td>
                   <td width=235 bgcolor=#FFFF99>銀行轉帳 --- 轉帳銀行代碼('.$res['bankcode'].')</td>
                 </tr>
                 <tr>
                   <td align=center>銀行轉帳帳戶</td>
                   <td>'.$res['vaccno'].'</td>
                 </tr>
                 <tr>
                   <td align=center>轉帳金額</td>
                   <td>'.intval($res['amt']).'元</td>
                 </tr>
                 <tr>
                   <td align=center>訂單編號</td>
                   <td>'.$res['od_sob'].'</td>
                 </tr>
                 <tr>
                   <td colspan=2><strong class=mycolor>請記下上列轉帳銀行代碼及轉帳帳戶,至最近的提款機台(ATM)操作轉帳,便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程</strong></td>
                 </tr>
                 <tr>
                   <td colspan=2 align=center class=myfont><br>
                     本線上金流機制採用&lt; <a href=http://www.ecbank.com.tw target=_blank>綠界科技 ECBank 線上支付平台</a> ,請安心使用
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
      global $order, $SID;

      $SID = twe_session_name() . '=' . twe_session_id();
      $mid = MODULE_PAYMENT_ECBANK_VACC_MID;

      $price = round($order->info['total']);
      $address = $order->customer['state'].$order->customer['city'].$order->customer['street_address'];
	  if ( MODULE_PAYMENT_ECBANK_VACC_RETUREURL != '') {$reurl = 'returl' ; $returl_var= MODULE_PAYMENT_ECBANK_VACC_RETUREURL ; }
	  
	  $ordnum = date('Ymdhis');
      $process_button_string =
        twe_draw_hidden_field('mer_id', $mid) .
		twe_draw_hidden_field('payment_type', 'web_atm') .
		twe_draw_hidden_field('setbank', 'ESUN') .
		twe_draw_hidden_field('od_sob',$ordnum).
		//twe_draw_hidden_field('item_name',$ordnum).
        twe_draw_hidden_field('amt', $price) .
		//twe_draw_hidden_field('returl' , twe_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')) .
		twe_draw_hidden_field('return_url' , twe_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')) .
		    //twe_draw_hidden_field('LG', UTF8) .
        twe_draw_hidden_field('訂購人', $order->customer['firstname']) .
        twe_draw_hidden_field('聯絡電話', $order->customer['telephone']) .
        twe_draw_hidden_field('送貨地址', $address) .
        twe_draw_hidden_field('email', $order->customer['email_address']) .
        twe_draw_hidden_field('郵遞區號', $order->customer['postcode']).
		
		
		twe_draw_hidden_field('systemtype', 'twe3.0_ecbank_vacc');
      return $process_button_string;
    }

    function before_process() {
      	 
    
	////////////////////////////////////////////
	//驗證碼
		$checkcode = MODULE_PAYMENT_ECBANK_VACC_CHECKCODE;
		
		
		// 組合字串
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']);
	
		// 回傳的交易驗證壓碼
		$tac = trim($_REQUEST['tac']);
		$c_order=trim($_REQUEST['od_sob']);

		$ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';
		$post_parm	=	'key='.$checkcode.
						'&serial='.$serial.
						'&tac='.$tac;

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
	/*	
   if($strAuth == 'valid=1') {
    			if($_POST['succ']==1) {
							return true;
						} else {
							$this->payment_error( 'payment_error=' . $this->code . '&error=2' );
													
						}
		} else {
		$this->payment_error( 'payment_error=' . $this->code . '&error=3' );
      		
		}	 
	*/	
    return true;
    }

	
    function get_error() {
	  	switch ($_REQUEST['error']) {
		  		case 1 :	$error = MODULE_PAYMENT_ECBANK_VACC_TEXT_ERROR_1 ;	break ;
		 	    case 2 :	$error = MODULE_PAYMENT_ECBANK_VACC_TEXT_ERROR_2 ;	break ;
		  		case 3 :	$error = MODULE_PAYMENT_ECBANK_VACC_TEXT_ERROR_3 ;	break ;
	  	}
      $error = array('title' => MODULE_PAYMENT_ECBANK_VACC_TEXT_ERROR,
                     'error' => $error );
      return $error;
    }

    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ECBANK_VACC_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
	global $db;
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ( 'MODULE_PAYMENT_ECBANK_VACC_STATUS', 'True', '6', '18', 'twe_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_ECBANK_VACC_ZONE', '0', '6', '18', 'twe_get_zone_class_title', 'twe_cfg_pull_down_zone_classes(', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_ECBANK_VACC_SORT_ORDER', '0', '6', '18', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_ECBANK_VACC_ORDER_STATUS_ID', '0', '6', '18', 'twe_cfg_pull_down_order_statuses(', 'twe_get_order_status_name', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ( 'MODULE_PAYMENT_ECBANK_VACC_MID', '輸入ECBank商店代號', '6', '18', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_ECBANK_VACC_CHECKCODE', '輸入交易加密私鑰', '6', '18', now())");
  	  $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_ECBANK_VACC_ALLOWED', '',   '6', '0', now())");
	  
    }

    function remove() {
	global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_ECBANK_VACC_STATUS', 'MODULE_PAYMENT_ECBANK_VACC_ALLOWED', 'MODULE_PAYMENT_ECBANK_VACC_ZONE', 'MODULE_PAYMENT_ECBANK_VACC_SORT_ORDER', 'MODULE_PAYMENT_ECBANK_VACC_ORDER_STATUS_ID', 'MODULE_PAYMENT_ECBANK_VACC_MID', 'MODULE_PAYMENT_ECBANK_VACC_CHECKCODE');
    }
  }
?>