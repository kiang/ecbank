<?php
class ControllerPaymentGwAllpay extends Controller {
	protected function index() {
		if(isset($this->session->data['doubleclick'])) unset($this->session->data['doubleclick']);
		
		//載入模組
		$this->load->model('checkout/order');
		$this->language->load('payment/gw_allpay');
		
		$od_sob = $this->session->data['order_id']; //取得訂單編號
		$order_info = $this->model_checkout_order->getOrder($od_sob);//取得訂單資訊
		$client = $this->config->get('gw_allpay_account'); //取得ECPAY商店代號
		$SMethod = $this->config->get('gw_allpay_stagemethod');//取得分期成本計算方法
		$cfgValue = $this->config->get('gw_allpay_cfg_value');//取得分期利率
                $checkoutTotal = intval(ceil($order_info['total']));//(int)帳單總計
		$amount = $this->calculationPee($SMethod,$cfgValue,$order_info['total']);//計算分期刷卡消費金額
		$roturl = HTTPS_SERVER . 'index.php?route=payment/gw_allpay/callback'; //付款完畢觸發網址
		$bk_posturl = HTTPS_SERVER . 'index.php?route=payment/gw_allpay/bk_callback';
                $i_invoice = $this->config->get('gw_allpay_i_invoice');//電子發票
                $imer_id = $this->config->get('gw_allpay_imer_id');
                $delay = $this->config->get('gw_allpay_delay');//電子發票延遲天數
                $email = $this->customer->getEmail();
                $products = $this->cart->getProducts();     
                $shipping_fee = $this->db->query("SELECT value from `" . DB_PREFIX . "order_total` WHERE order_id = '" .$od_sob. "' and title = '".$order_info['shipping_method']."'");
		
		$this->data['total'] = $checkoutTotal;//定義交易金額
		$this->data['credit_total'] = $amount;//刷卡金額
		$this->data['credit_fee'] = intval(ceil($amount-$checkoutTotal));//刷卡費用
		
		//定義表單
		$this->data['def_url']  = "<form name='form1' method='post' action='https://credit.allpay.com.tw/form_Sc_to5.php'>";
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
                    $this->data['def_url'] .= "<input type='hidden' name='prd_name[]' value='運費'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_qry[]' value='1'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_price[]' value='".round($shipping_fee->row['value'])."'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_name[]' value='手續費'>";
                    $this->data['def_url'] .= "<input type='hidden' name='prd_qry[]' value='1'>";
                    if ($SMethod == 'add'){//手續費外加
                        $this->data['def_url'] .= "<input type='hidden' name='prd_price[]' value='".($this->data['credit_fee'])."'>";
                    }
                    else{//手續費內含
                        $this->data['def_url'] .= "<input type='hidden' name='prd_price[]' value='0'>";
                    }
                }                
                
		$this->data['def_url'] .= "</form>";
		
		//載入語言資訊
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');
		$this->data['text_payment'] = $this->language->get('text_payment');
		$this->data['text_instruction'] = $this->language->get('text_instruction');
		$this->data['text_creditfee'] = $this->language->get('text_creditfee');
		$this->data['text_credittotal'] = $this->language->get('text_credittotal');
		$this->data['text_total_error'] = $this->language->get('text_total_error');
		$this->data['text_symboleft'] = $this->currency->getSymbolLeft();
		$this->data['gw_allpay_description'] = nl2br($this->config->get('gw_allpay_description_' . $this->config->get('config_language_id')));
		
		//$this->data['continue'] = HTTPS_SERVER . 'index.php?route=checkout/gw_allpay_respond';//完成結帳網址
		
		//載入樣板
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/gw_allpay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/gw_allpay.tpl';
		} else {
			$this->template = 'default/template/payment/gw_allpay.tpl';
		}
		
		//輸出頁面資訊
		$this->render();
	}
	
	public function confirm(){
		$doubleclick = mt_rand(0,1000000);
		if(!isset($this->session->data['doubleclick']) && isset($this->session->data['order_id'])){
			$this->session->data['doubleclick'] = $doubleclick;
			
			//載入模組
			$this->load->model('checkout/order');
			
			$od_sob = $this->session->data['order_id'];//取得訂單編號
			$order_info = $this->model_checkout_order->getOrder($od_sob);//取得訂單資訊
			$orderStatus = $this->config->get('gw_allpay_order_status_id');//取得預設訂單狀態
			$SMethod = $this->config->get('gw_allpay_stagemethod');//取得分期成本計算方法
			$cfgValue = $this->config->get('gw_allpay_cfg_value');//取得分期利率
			$amount = $this->calculationPee($SMethod,$cfgValue,$order_info['total']);//計算刷卡金額
			
			//新增訂單資訊
			$this->model_checkout_order->confirm($od_sob, $orderStatus, $order_info['comment']);
			//修正定單價錢
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '".$amount."', date_modified = NOW() WHERE order_id = '" . $this->session->data['order_id'] . "'");
		}
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
			$c_checkcode = $this->config->get('gw_allpay_checkcode'); //綠界 ECPAY 檢查碼
			$TOkSi = $_POST['process_time'] + $_POST['gwsr'] + $_POST['amount'];
			$my_spcheck = $this->gwSpcheck($c_checkcode, $TOkSi);
			$ecbank_gateway = HTTP_SERVER . 'index.php?route=checkout/gw_allpay_respond';
			$ecpay_result = "<form name='form1' action='" . $ecbank_gateway . "' method='post'>";
			if (
				$order_info &&
				$_POST['succ'] == '1' &&
				$my_spcheck == $_POST['spcheck'] &&
				isset($this->session->data['order_id'])
			) {
				$ecpay_result .= "<input type='hidden' name='res' value='succ'>";
				$comment = $order_info['comment'];
				if ($order_info['order_status_id'] != 13)
					$this->model_checkout_order->update($_POST['od_sob'], '13', $comment);
			} else {
				$ecpay_result .= "<input type='hidden' name='res' value='error'>";
			}
			$ecpay_result .= "</form>";
			echo $ecpay_result;
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
			$c_checkcode = $this->config->get('gw_allpay_checkcode'); //綠界 ECPAY 檢查碼
			$TOkSi = $_POST['process_time'] + $_POST['gwsr'] + $_POST['amount'];
			$my_spcheck = $this->gwSpcheck($c_checkcode, $TOkSi);
			if (
				$_POST['succ'] == '1' &&
				$my_spcheck == $_POST['spcheck'] &&
				$order_info['total'] == $_POST['amount']
			) {
				$comment = '' . $order_info['comment'];
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
			$amount = $total/$_num;//分期內含交易金額
		}else if($method == "contain" && $total >= 1){
			$amount = $total;//分期外加交易金額
		}else{
			exit("data error...");
		}
		return intval(ceil($amount));
	}
}
?>
