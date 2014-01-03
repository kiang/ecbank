<?php
class ControllerPaymentEcbankPaypal extends Controller {
	protected function index() {
	
		$this->language->load('payment/ecbank_paypal');
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['total'] = intval(round($order_info['total']));
		//$this->data['total'] = (int)$order_info['total'];
		
		$payment_type = 'paypal'; // ECBank PayPal
		$mer_id = $this->config->get('ecbank_paypal_account'); // 您的ECBank商店代號
		$od_sob = $this->session->data['order_id']; //賣家自訂交易編號
		$item_name = $this->session->data['order_id']; //交易項目名稱
		$cur_type = $this->config->get('ecbank_paypal_cur_type'); //貨幣類別
		$amt = intval(round($order_info['total'])); // 交易金額
		//$amt = (int)$order_info['total']; // 交易金額
		$return_url = HTTPS_SERVER . 'index.php?option=com_aceshop&route=payment/ecbank_paypal/callback'; // 付款完成通知網址
		$cancel_url = HTTPS_SERVER . 'index.php/category'; // 取消後導回網址
                $i_invoice = $this->config->get('ecbank_paypal_i_invoice'); // 是否使用電子發票       
                $imer_id = $this->config->get('ecbank_paypal_imer_id'); // 電子發票商店代號
                $delay = $this->config->get('ecbank_paypal_delay'); // 電子發票開立延遲天數   
                $email = $this->customer->getEmail();//取得客戶email
                $products = $this->cart->getProducts();          
                $shipping_fee = $this->db->query("SELECT value from `" . DB_PREFIX . "order_total` WHERE order_id = '" .$od_sob. "' and title = '".$order_info['shipping_method']."'");                
		
		$this->data['def_url']  = "<form name='form1' style='text-align:center;' method=post action='https://ecbank.com.tw/gateway.php'>";
		$this->data['def_url'] .= "<input type='hidden' name='mer_id' value='".$mer_id."'>";
		$this->data['def_url'] .= "<input type='hidden' name='payment_type' value='".$payment_type."'>";
		$this->data['def_url'] .= "<input type='hidden' name='od_sob' value='".$od_sob."'>";
		$this->data['def_url'] .= "<input type='hidden' name='item_name' value='".$item_name."'>";
		$this->data['def_url'] .= "<input type='hidden' name='cur_type' value='".$cur_type."'>";
		$this->data['def_url'] .= "<input type='hidden' name='amt' value='".$amt."'>";
		$this->data['def_url'] .= "<input type='hidden' name='return_url' value='".$return_url."'>";
		$this->data['def_url'] .= "<input type='hidden' name='cancel_url' value='".$cancel_url."'>";
                if($i_invoice == 'yes'){ 
                    $this->data['def_url'] .= "<input type='hidden' name='inv_active' value=1>";
                    $this->data['def_url'] .= "<input type='hidden' name='inv_mer_id' value='".$imer_id."'>";
                    $this->data['def_url'] .= "<input type='hidden' name='inv_amt' value='".$amount."'>";
                    $this->data['def_url'] .= "<input type='hidden' name='inv_semail' value='".$email."'>";
                    $this->data['def_url'] .= "<input type='hidden' name='inv_delay' value='".$delay."'>";
                    foreach($products as $p){
                        $this->data['def_url'] .= "<input type='hidden' name='prd_name[]' value='".$p['name']."'>";
                        $this->data['def_url'] .= "<input type='hidden' name='prd_qry[]' value='".$p['quantity']."'>";
                        $this->data['def_url'] .= "<input type='hidden' name='prd_price[]' value='".$p['price']."'>";
                    }
                    $this->data['def_url'] .= "<input type='hidden' name='prd_name[]' value=運費>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_qry[]' value='1'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_price[]' value='".round($shipping_fee->row['value'])."'>";
                } 
                
		$this->data['def_url'] .= "</form>";		

		if(isset($this->session->data['doubleclick'])) unset($this->session->data['doubleclick']);
		
		$this->data['text_payment'] = $this->language->get('text_payment');
		$this->data['text_instruction'] = $this->language->get('text_instruction');
		$this->data['text_total_error'] = $this->language->get('text_total_error');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		
		$this->data['ecbank_paypal_description'] = nl2br($this->config->get('ecbank_paypal_description_' . $this->config->get('config_language_id')));
		
		$this->data['continue'] = $this->url->link('checkout/ecbank_pincode_success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/ecbank_paypal.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/ecbank_paypal.tpl';
		} else {
			$this->template = 'default/template/payment/ecbank_paypal.tpl';
		}	
		
		$this->render(); 
	}
	
	public function confirm() {
		$doubleclick = mt_rand(0,1000000);
		if(!isset($this->session->data['doubleclick'])){
			$this->session->data['doubleclick'] = $doubleclick;
		}
	}
	
	public function callback() {
		$enc_key = $this->config->get('ecbank_paypal_checkcode'); // 商店設定在ECBank管理後台的交易加密私鑰
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr']); // 組合字串
		$tac = trim($_REQUEST['tac']); // 回傳的交易驗證壓碼
		
		// 找出訂單金額
		$query = $this->db->query("SELECT total from `" . DB_PREFIX . "order` WHERE order_id = '" . $_REQUEST['od_sob'] . "'");

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
		
		$ecbank_gateway = HTTP_SERVER . 'index.php?option=com_aceshop&route=checkout/ecbank_paypal_success';
		$ecbank_paypal_result = "<form name='form1' action='".$ecbank_gateway."' method='post'>";

		if($tac_valid == 'valid=1'){
			if($_REQUEST['succ']=='1' && (int)$query->row['total'] == intval($_REQUEST['amt'])) {
				$comment = '';
				$this->load->model('checkout/order');
				$this->model_checkout_order->confirm($_REQUEST['od_sob'], $this->config->get('ecbank_paypal_order_status_id'), $comment);
				
				if (isset($this->session->data['order_id'])){
					$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '13', date_modified = NOW() WHERE order_id = '" . $_REQUEST['od_sob'] . "'");
					$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . $_REQUEST['od_sob'] . "', order_status_id = '13', notify = '0', comment = '" . $comment . "', date_added = NOW()");
				}
				//echo 'OK';
				$ecbank_paypal_result .= "<input type='hidden' name='res' value='succ'>";
			}
		} else {
			//echo 'FAIL';
			$ecbank_paypal_result .= "<input type='hidden' name='res' value='error'>";
		}
		$ecbank_paypal_result .= "</form>";
		echo $ecbank_paypal_result;
		echo "<script language='javascript'>setTimeout('document.form1.submit()',0);</script>";
		exit;
	}
}
?>