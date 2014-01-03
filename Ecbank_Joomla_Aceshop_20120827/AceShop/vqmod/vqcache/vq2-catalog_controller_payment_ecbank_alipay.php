<?php
class ControllerPaymentEcbankAlipay extends Controller {
	protected function index() {
		$this->language->load('payment/ecbank_alipay');
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['total'] = intval(round($order_info['total']));
		//$this->data['total'] = (int)$order_info['total'];
		
		$payment_type = 'alipay'; // ECBank alipay
		$mer_id = $this->config->get('ecbank_alipay_account'); // 您的ECBank商店代號
		$enc_key = $this->config->get('ecbank_alipay_checkcode'); // 商店設定在ECBank管理後台的交易加密私鑰                
		$od_sob = $this->session->data['order_id']; //賣家自訂交易編號
		$amt = intval(round($order_info['total'])); // 交易金額
		//$amt = (int)$order_info['total']; // 交易金額
		$return_url = $this->url->link('payment/ecbank_alipay/callback');// 付款完成通知網址
                $type = 'upload_goods';
                $goods_href = 'http://';
                $ecbank_gateway = 'https://ecbank.com.tw/web_service/alipay_goods_upload.php';
                $email = $this->customer->getEmail();
                $i_invoice = $this->config->get('gw_alipay_i_invoice');//電子發票
                $imer_id = $this->config->get('gw_alipay_imer_id');
                $delay = $this->config->get('gw_alipay_delay');//電子發票延遲天數   
                $shipping_fee = $this->db->query("SELECT value from `" . DB_PREFIX . "order_total` WHERE order_id = '" .$od_sob. "' and title = '".$order_info['shipping_method']."'");
		$products = $this->cart->getProducts();                

                // 使用curl上傳商品

               foreach($products as $p){
                $post_str='enc_key='.$enc_key.
                          '&mer_id='.$mer_id.
                          '&type='.$type.
                          '&goods_id='.$p['product_id'].
                          '&goods_title='.$p['name'].
                          '&goods_price='.$p['price'].
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
                }
                curl_close($ch);
		$this->data['def_url']  = "<form name='form1' style='text-align:center;' method=post action='https://ecbank.com.tw/gateway.php'>";
		$this->data['def_url'] .= "<input type='hidden' name='mer_id' value='".$mer_id."'>";
		$this->data['def_url'] .= "<input type='hidden' name='payment_type' value='".$payment_type."'>";
		$this->data['def_url'] .= "<input type='hidden' name='od_sob' value='".$od_sob."'>";
		$this->data['def_url'] .= "<input type='hidden' name='amt' value='".$amt."'>";
		$this->data['def_url'] .= "<input type='hidden' name='ok_url' value='".$return_url."'>";
                foreach($products as $p){
                    $this->data['def_url'] .= "<input type='hidden' name='goods_name[]' value='".$p['product_id']."'>";
                    $this->data['def_url'] .= "<input type='hidden' name='goods_amount[]' value='".$p['quantity']."'>";
                }
                if($i_invoice == 'yes'){ //電子發票
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
                    $this->data['def_url'] .= "<input type='hidden' name='prd_name[]' value='shipping_fee'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_qry[]' value='1'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_price[]' value='".round($shipping_fee->row['value'])."'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_name[]' value='credit_fee'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_qry[]' value='1'>";
                    if ($SMethod == 'add'){//手續費外加
                        $this->data['def_url'] .= "<input type='hidden' name='prd_price[]' value='".round($this->data['credit_fee'])."'>";
                    }
                    else{//手續費內含
                        $this->data['def_url'] .= "<input type='hidden' name='prd_price[]' value='0'>";
                    }
                }
		$this->data['def_url'] .= "</form>";
		$this->data['button_confirm'] = $this->language->get('button_confirm');
				
		if(isset($this->session->data['doubleclick'])) unset($this->session->data['doubleclick']);
		
		$this->data['text_payment'] = $this->language->get('text_payment');
		$this->data['text_instruction'] = $this->language->get('text_instruction');
		$this->data['text_total_error'] = $this->language->get('text_total_error');
		
		$this->data['ecbank_alipay_description'] = nl2br($this->config->get('ecbank_alipay_description_' . $this->config->get('config_language_id')));
		
		$this->data['continue'] = $this->url->link('checkout/ecbank_alipay_success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/ecbank_alipay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/ecbank_alipay.tpl';
		} else {
			$this->template = 'default/template/payment/ecbank_alipay.tpl';
		}	
		
		$this->render(); 
	}
	
	public function confirm() {
		$doubleclick = mt_rand(0,1000000);
		if(!isset($this->session->data['doubleclick'])){
			$this->session->data['doubleclick'] = $doubleclick;
			
			$this->language->load('payment/ecbank_alipay');
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('ecbank_alipay_order_status_id'), $comment);
		}
	}
	public function callback() {
		$enc_key = $this->config->get('ecbank_alipay_checkcode'); // 商店設定在ECBank管理後台的交易加密私鑰
		$serial = trim($_REQUEST['proc_date'].$_REQUEST['proc_time'].$_REQUEST['tsr'].$_REQUEST['od_sob'].$_REQUEST['amt']); // 組合字串
		$mac = trim($_REQUEST['mac']); // 回傳的交易驗證壓碼
		
		// 找出訂單金額
		$query = $this->db->query("SELECT total from `" . DB_PREFIX . "order` WHERE order_id = '" . $_REQUEST['od_sob'] . "'");
                $post_str='key='.$enc_key.
                          '&serial='.$serial.
                          '&mac='.$mac;
		$comment = '';
		foreach($_REQUEST as $keyx => $val){
			$comment .= $keyx.'='.$val.'&';
		}
		$comment = substr($comment, 0 ,-1);

		// ECBank 驗證Web Service網址
		$ws_url = 'https://ecbank.com.tw/web_service/get_outmac_valid_new.php';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$ws_url);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$post_str);
		$tac_valid=curl_exec($ch);
		
                $ecbank_gateway = HTTP_SERVER . 'index.php?route=checkout/ecbank_alipay_success';
		$ecbank_alipay_result = "<form name='form1' action='".$ecbank_gateway."' method='post'>";
                
		if($tac_valid == 'valid=1'){
			if($_REQUEST['succ']=='1' && (int)$query->row['total'] == intval($_REQUEST['amt'])) {
				$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '13', date_modified = NOW() WHERE order_id = '" . $_REQUEST['od_sob'] . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . $_REQUEST['od_sob'] . "', order_status_id = '13', notify = '0', comment = '" . $comment . "', date_added = NOW()");
				echo 'OK';
                                $ecbank_alipay_result .= "<input type='hidden' name='res' value='succ'>";
			}
		} else {
			echo 'FAIL';
                        $ecbank_alipay_result .= "<input type='hidden' name='res' value='error'>";
		}
                $ecbank_alipay_result .= "</form>";
		echo $ecbank_alipay_result;
		echo "<script language='javascript'>setTimeout('document.form1.submit()',0);</script>";
		exit;
	}
}
?>