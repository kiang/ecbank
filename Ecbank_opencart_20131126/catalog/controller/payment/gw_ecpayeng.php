<?php
class ControllerPaymentGwEcpayeng extends Controller {
	protected function index() {
		$this->language->load('payment/gw_ecpayeng');
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['total'] = intval(round($order_info['total']));
		//$this->data['total'] = (int)$order_info['total'];
		
		$client = $this->config->get('gw_ecpayeng_account'); // 您的ECPAY商店代號
		$SMethod = $this->config->get('gw_ecpayeng_stagemethod');//取得分期成本計算方法
		$cfgValue = $this->config->get('gw_ecpayeng_cfg_value');//取得分期利率
                $checkoutTotal = intval(ceil($order_info['total']));//(int)帳單總計
		$amount = $this->calculationPee($SMethod,$cfgValue,$order_info['total']);//計算分期刷卡消費金額
		//$amount = (int)$order_info['total']; // 交易金額
		$od_sob = $this->session->data['order_id']; //賣家自訂交易編號
		$roturl = HTTPS_SERVER . 'index.php?route=payment/gw_ecpayeng/callback'; // 回傳網址
		$bk_posturl = HTTPS_SERVER . 'index.php?route=payment/gw_ecpayeng/bk_callback';
		$mallurl = HTTP_SERVER . 'index.php?route=checkout/gw_ecpayeng_respond/unionpay';
                $i_invoice = $this->config->get('gw_ecpayeng_i_invoice');//電子發票
                $imer_id = $this->config->get('gw_ecpayeng_imer_id');
                $delay = $this->config->get('gw_ecpayeng_delay');//電子發票延遲天數
                $email = $this->customer->getEmail();
                $products = $this->cart->getProducts();
                $shipping_fee = $this->db->query("SELECT value from `" . DB_PREFIX . "order_total` WHERE order_id = '" .$od_sob. "' and title = '".$order_info['shipping_method']."'");
		$this->data['total'] = $checkoutTotal;//定義交易金額
		$this->data['credit_total'] = $amount;//刷卡金額
		$this->data['credit_fee'] = intval(ceil($amount-$checkoutTotal));//刷卡費用                 
		
		//定義表單
		$this->data['def_url']  = "<form name='form1' method='post' action='https://ecpay.com.tw/form_Sc_to5e.php'>";
		$this->data['def_url'] .= "<input type='hidden' name='act' value='auth'>";
		$this->data['def_url'] .= "<input type='hidden' name='client' value='".$client."'>";
		$this->data['def_url'] .= "<input type='hidden' name='amount' value='".$amount."'>";
		$this->data['def_url'] .= "<input type='hidden' name='od_sob' value='".$od_sob."'>";
		$this->data['def_url'] .= "<input type='hidden' name='roturl' value='".$roturl."'>";
		$this->data['def_url'] .= "<input type='hidden' name='bk_posturl' value='".$bk_posturl."'>";
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
                    $this->data['def_url'] .= "<input type='hidden' name='prd_name[]' value=手續費>";
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
		$this->data['button_back'] = $this->language->get('button_back');
                $this->data['text_payment'] = $this->language->get('text_payment');
		$this->data['text_instruction'] = $this->language->get('text_instruction');
		$this->data['text_total_error'] = $this->language->get('text_total_error');
                $this->data['text_creditfee'] = $this->language->get('text_creditfee');
		$this->data['text_credittotal'] = $this->language->get('text_credittotal');
		$this->data['text_total_error'] = $this->language->get('text_total_error');
		$this->data['text_symboleft'] = $this->currency->getSymbolLeft();
                
                if(isset($this->session->data['doubleclick'])) unset($this->session->data['doubleclick']);
		$this->data['gw_ecpayeng_description'] = nl2br($this->config->get('gw_ecpayeng_description_' . $this->config->get('config_language_id')));
		
		$this->data['continue'] = HTTPS_SERVER . 'index.php?route=checkout/gw_ecpayeng_respond';
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/gw_ecpayeng.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/gw_ecpayeng.tpl';
		} else {
			$this->template = 'default/template/payment/gw_ecpayeng.tpl';
		}
		$this->render(); 
	}
	public function confirm() {
		$doubleclick = mt_rand(0,1000000);
		if(!isset($this->session->data['doubleclick'])){
			$this->session->data['doubleclick'] = $doubleclick;
			//載入模組
			$this->load->model('checkout/order');
			$od_sob = $this->session->data['order_id']; //取得訂單編號
			$order_info = $this->model_checkout_order->getOrder($od_sob); //取得訂單資訊
                        $orderStatus = $this->config->get('gw_ecpayeng_order_status_id'); //取得預設訂單狀態
                        $SMethod = $this->config->get('gw_ecpayeng_stagemethod');//取得分期成本計算方法
                        $cfgValue = $this->config->get('gw_ecpayeng_cfg_value');//取得分期利率
                        $amount = $this->calculationPee($SMethod,$cfgValue,$order_info['total']);//計算刷卡金額
			$this->model_checkout_order->confirm($od_sob, $orderStatus, $order_info['comment']);
			//修正定單價錢
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '".$amount."', date_modified = NOW() WHERE order_id = '" . $this->session->data['order_id'] . "'");
		}
		exit();
	}
	//ECPay 前景觸發
	public function callback() {
		if (
			array_key_exists('process_time', $_POST) &&
			array_key_exists('gwsr', $_POST) &&
			array_key_exists('amount', $_POST) &&
			array_key_exists('spcheck', $_POST) &&
			array_key_exists('succ', $_POST) &&
			array_key_exists('od_sob', $_POST)
		) {
			$this->load->model('checkout/order');
			$od_sob = $_POST['od_sob'];
			$order_info = $this->model_checkout_order->getOrder($od_sob);
			$c_checkcode = $this->config->get('gw_ecpayeng_checkcode'); //綠界 ECPAY 檢查碼
			$TOkSi = $_POST['process_time'] + $_POST['gwsr'] + $_POST['amount'];
			$my_spcheck = $this->gwSpcheck($c_checkcode, $TOkSi);
			$ecbank_gateway = HTTP_SERVER . 'index.php?route=checkout/gw_ecpayeng_respond';
			$ecpayeng_result = "<form name='form1' action='" . $ecbank_gateway . "' method='post'>";
			if (
				$order_info &&
				$_POST['succ'] == '1' &&
				$my_spcheck == $_POST['spcheck'] &&
				isset($this->session->data['order_id'])
			) {
				$ecpayeng_result .= "<input type='hidden' name='res' value='succ'>";
				$comment = '付款成功'.$order_info['comment'];
				if ($order_info['order_status_id'] != 13)
					$this->model_checkout_order->update($_POST['od_sob'], '13', $comment);
			} else {
				$ecpayeng_result .= "<input type='hidden' name='res' value='error'>";
			}
			$ecpayeng_result .= "</form>";
			echo $ecpayeng_result;
			echo "<script language='javascript'>setTimeout('document.form1.submit()',0);</script>";
		}
		exit();
	}
	//ECPAY 背景觸發
	public function bk_callback() {
		if (
			array_key_exists('process_time', $_POST) &&
			array_key_exists('gwsr', $_POST) &&
			array_key_exists('amount', $_POST) &&
			array_key_exists('spcheck', $_POST) &&
			array_key_exists('succ', $_POST) &&
			array_key_exists('od_sob', $_POST)
		) {
			$this->load->model('checkout/order');
			$od_sob = $_POST['od_sob'];
			$order_info = $this->model_checkout_order->getOrder($od_sob);
			$c_checkcode = $this->config->get('gw_ecpayeng_checkcode'); //綠界 ECPAY 檢查碼
			$TOkSi = $_POST['process_time'] + $_POST['gwsr'] + $_POST['amount'];
			$my_spcheck = $this->gwSpcheck($c_checkcode, $TOkSi);
			if (
				$_POST['succ'] == '1' &&
				$my_spcheck == $_POST['spcheck'] &&
				$order_info['total'] == $_POST['amount']
			) {
				$comment = '付款成功' . $order_info['comment'];
				if ($order_info['order_status_id'] != 13)
					$this->model_checkout_order->update($od_sob, '13', $comment);
			}
		}
		exit();
	}
	private function gwSpcheck($s, $U) { //算出認證用的字串
		$a = substr($U, 0, 1) . substr($U, 2, 1) . substr($U, 4, 1); //取出檢查碼的跳字組合 1,3,5 字元
		$b = substr($U, 1, 1) . substr($U, 3, 1) . substr($U, 5, 1); //取出檢查碼的跳字組合 2,4,6 字元
		$c = ( $s % $U ) + $s + $a + $b; //取餘數 + 檢查碼 + 奇位跳字組合 + 偶位跳字組合
		return $c;
	}
	private function calculationPee($method,$cfgv,$total){
		$amount = 0;
		if($method == "add" && $cfgv < 1 &&  $cfgv >= 0  && $total >= 1 ){
			$_num = 1-$cfgv;
			$amount = $total/$_num;//分期外加交易金額
		}else if($method == "contain" && $total >= 1){
			$amount = $total;//分期內含交易金額
		}else{
			exit("data error...");
		}
		return intval(ceil($amount));
	}        
}
?>
