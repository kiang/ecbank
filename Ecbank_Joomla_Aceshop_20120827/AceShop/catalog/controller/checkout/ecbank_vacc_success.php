<?php 
class ControllerCheckoutEcbankVaccSuccess extends Controller { 
	public function index() {
		if(!isset($this->session->data['ecbank_vacc_error'])) header('location:'.$this->url->link('common/home'));
		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();
			
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);	
			unset($this->session->data['coupon']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			
			unset($this->session->data['doubleclick']);
		}
		$this->data['error'] = $this->session->data['ecbank_vacc_error'];
		$this->data['error_message'] = $this->session->data['ecbank_vacc_error_message'];
		$this->data['od_sob'] = $this->session->data['ecbank_vacc_od_sob'];
		if(isset($this->session->data['ecbank_vacc_bankcode'])) $this->data['bankcode'] = $this->session->data['ecbank_vacc_bankcode'];
		if(isset($this->session->data['ecbank_vacc_vaccno'])) $this->data['vaccno'] = $this->session->data['ecbank_vacc_vaccno'];
						   
		$this->language->load('checkout/ecbank_vacc_success');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['breadcrumbs'] = array(); 

      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('common/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	); 
		
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/cart'),
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	);
				
		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'text'      => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		);	
					
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/ecbank_vacc_success'),
        	'text'      => $this->language->get('text_success'),
        	'separator' => $this->language->get('text_separator')
      	);
		
    	$this->data['heading_title'] = $this->language->get('heading_title');

		if ($this->customer->isLogged()) {
    		$this->data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
		} else {
    		$this->data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}
		$this->data['heading_title_error'] = $this->language->get('heading_title_error');
		$this->data['text_title'] = $this->language->get('text_title');
		$this->data['text_message1'] = $this->language->get('text_message1');
		$this->data['text_message2'] = $this->language->get('text_message2');
		$this->data['text_message3'] = $this->language->get('text_message3');		
		
    	$this->data['button_continue'] = $this->language->get('button_continue');

    	$this->data['continue'] = $this->url->link('common/home');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/ecbank_vacc_success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/ecbank_vacc_success.tpl';
		} else {
			$this->template = 'default/template/common/ecbank_vacc_success.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
		
		$this->response->setOutput($this->render());
  	}
	
	public function home() {
		unset($this->session->data['ecbank_vacc_error']);
		unset($this->session->data['ecbank_vacc_error_message']);
		unset($this->session->data['ecbank_vacc_payno']);
	}
}
?>