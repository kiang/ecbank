<?php
class ControllerPaymentEcbankPincode extends Controller {
	protected function index() {
		$this->language->load('payment/ecbank_pincode');		
			
		if(isset($this->session->data['doubleclick'])) unset($this->session->data['doubleclick']);
		if(isset($this->session->data['ecbank_pincode_error'])) unset($this->session->data['ecbank_pincode_error']);
		if(isset($this->session->data['ecbank_pincode_error_message'])) unset($this->session->data['ecbank_pincode_error_message']);
		if(isset($this->session->data['ecbank_pincode_payno'])) unset($this->session->data['ecbank_pincode_payno']);
		
		$this->data['text_payment'] = $this->language->get('text_payment');
		$this->data['text_instruction'] = $this->language->get('text_instruction');		
		$this->data['text_total_error'] = $this->language->get('text_total_error');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		
		$this->data['ecbank_vacc_description'] = nl2br($this->config->get('ecbank_pincode_description_' . $this->config->get('config_language_id')));
		
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$this->data['total'] = intval(round($order_info['total']));
		
		$this->data['continue'] = $this->url->link('checkout/ecbank_pincode_success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/ecbank_pincode.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/ecbank_pincode.tpl';
		} else {
			$this->template = 'default/template/payment/ecbank_pincode.tpl';
		}	
		
		$this->render(); 
	}
	
	public function confirm() {
		$doubleclick = mt_rand(0,1000000);
		if(!isset($this->session->data['doubleclick'])){
			$this->session->data['doubleclick'] = $doubleclick;
			
			$this->language->load('payment/ecbank_pincode');
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			
			$payment_type = 'cvs'; //ECBank超商代碼[全家、萊爾富]
			$mer_id = $this->config->get('ecbank_pincode_account'); // 您的ECBank商店代號
			$enc_key = $this->config->get('ecbank_pincode_checkcode'); // 商店設定在ECBank管理後台的交易加密私鑰
			$od_sob = $this->session->data['order_id']; //賣家自訂交易編號
			$amt = intval(round($order_info['total'])); // 繳費金額
			//$prd_desc = rawurlencode(trim(str_replace(' ','',$this->config->get('config_name')))); // 商店名稱
			$prd_desc = rawurlencode(trim($this->config->get('config_name'))); // 商店名稱
			$ok_url =rawurlencode(HTTPS_SERVER . 'index.php?route=payment/ecbank_pincode/callback'); // 付款完成通知網址
                        $i_invoice = $this->config->get('ecbank_pincode_i_invoice'); // 是否使用電子發票
                        $imer_id = $this->config->get('ecbank_pincode_imer_id'); // 電子發票商店代號
                        $delay = $this->config->get('ecbank_pincode_delay'); // 電子發票開立延遲天數  
						$shipping_fee = $this->db->query("SELECT value from `" . DB_PREFIX . "order_total` WHERE order_id = '" .$od_sob. "' and title = '".$order_info['shipping_method']."'");
                        $email = $this->customer->getEmail();//取得客戶email
                        $products = $this->cart->getProducts();                         
/*
			// ECBank 超商代碼繳費代碼取號網址
			$ecbank_auth_url = 'https://ecbank.com.tw/gateway.php?payment_type='.$payment_type.
					 '&mer_id='.$mer_id.
					 '&enc_key='.$enc_key.
					 '&od_sob='.$od_sob.
					 '&amt='.$amt.
					 '&prd_desc='.$prd_desc.
					 '&ok_url='.$ok_url;

			// 以GET方式背景取號 (也可以使用curl)
			$strAuth = file_get_contents($ecbank_auth_url);

			// 分解字串
			parse_str($strAuth, $res);
			
			$comment = '';
			// 判斷取號結果，完整回傳參數請參考2-2
			if(!isset($res['error']) || $res['error'] != '0') {
				$this->session->data['ecbank_pincode_error'] = true;
				$this->session->data['ecbank_pincode_error_message'] = $this->language->get('text_error').'('.$res['error'].')';
				$this->session->data['ecbank_pincode_payno'] = 'error';
				$comment .= $this->language->get('text_error').'('.$res['error'].')';
			} else {
				$this->session->data['ecbank_pincode_error'] = false;
				$this->session->data['ecbank_pincode_error_message'] = '';
				$this->session->data['ecbank_pincode_payno'] = $res['payno'];
				$comment .= $this->language->get('text_message1') . $res['payno'] . $this->language->get('text_message2');
			}
			
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('ecbank_pincode_order_status_id'), $comment);
		}*/
		//ECBank IBON取號參數串接
			$post_str ='mer_id='.$mer_id.
				'&payment_type='.$payment_type.
				'&prd_desc='.$prd_desc.
				'&enc_key='.$enc_key.
				'&od_sob='.$od_sob.
				'&amt='.$amt.
				'&ok_url='.$ok_url;
                        if($i_invoice == 'yes'){ //電子發票
                            $post_str .= '&inv_active=1'.
                                         '&inv_mer_id='.$imer_id.
                                         '&inv_semail='.$email.
                                         '&inv_amt='.$amt;
                            foreach($products as $p){
                                $post_str .= '&prd_name[]='.$p['name'].
                                             '&prd_qry[]='.$p['quantity'].
                                             '&prd_price[]='.$p['price'];
                            }
                            $post_str .= '&prd_name[]=運費'.
                                         '&prd_qry[]=1'.
                                         '&prd_price[]='.round($shipping_fee->row['value']);
                        }                        

			// 以curl方式背景取號
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_PORT, 443);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL,'https://ecbank.com.tw/gateway.php');
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
			$strAuth = curl_exec($ch);
			if (curl_errno($ch)) {
				$strAuth = false;
			}
			curl_close($ch);
			$comment = '';
			if($strAuth) {
				// 分解字串
				parse_str($strAuth, $res);
				// 判斷取號結果
				if(!isset($res['error']) || $res['error'] != '0'){
					$this->session->data['ecbank_pincode_error'] = true;
					$this->session->data['ecbank_pincode_error_message'] = $this->language->get('text_error').'('.$res['error'].')';
					$this->session->data['ecbank_pincode_payno'] = 'error';
					$comment .= $this->language->get('text_error').'('.$res['error'].')';
				}else {
					$this->session->data['ecbank_pincode_error'] = false;
					$this->session->data['ecbank_pincode_error_message'] = '';
					$this->session->data['ecbank_pincode_payno'] = $res['payno'];
					$comment .= $this->language->get('text_message1') . $res['payno'] . $this->language->get('text_message2');
				}
			} else {
				// 取號錯誤
				$comment .= $this->language->get('text_error').'('.$this->language->get('text_number_error').')';
			}
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('ecbank_pincode_order_status_id'), $comment);
		}
	}
	
	public function callback() {
		$enc_key = $this->config->get('ecbank_pincode_checkcode'); // 商店設定在ECBank管理後台的交易加密私鑰
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']); // 組合字串
		$tac = trim($_REQUEST['tac']); // 回傳的交易驗證壓碼
		
		// 找出訂單金額
		$query = $this->db->query("SELECT total from `" . DB_PREFIX . "order` WHERE order_id = '" . $_REQUEST['od_sob'] . "'");

		$comment = '';
		foreach($_REQUEST as $keyx => $val){
			$comment .= $keyx.'='.$val.'&';
		}
		$comment = substr($comment, 0 ,-1);

		// ECBank 驗證Web Service網址
		$ws_url = 'https://ecbank.com.tw/web_service/get_outmac_valid.php?key='.$enc_key.
				  '&serial='.$serial.
				  '&tac='.$tac;

		// 取得驗證結果 (也可以使用curl)
		$ch = curl_init();
		// 設定擷取的URL網址
		curl_setopt($ch, CURLOPT_URL, $ws_url );
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		//將curl_exec()獲取的訊息以文件流的形式返回，而不是直接輸出。
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		// 執行
		$tac_valid=curl_exec($ch);
		
		if($tac_valid == 'valid=1'){
			if($_REQUEST['succ']=='1' && (int)$query->row['total'] == intval($_REQUEST['amt'])) {
				$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '13', date_modified = NOW() WHERE order_id = '" . $_REQUEST['od_sob'] . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . $_REQUEST['od_sob'] . "', order_status_id = '13', notify = '0', comment = '" . $comment . "', date_added = NOW()");
				echo 'OK';
			}
		} else {
			echo 'FAIL';
		}
	}
}
?>