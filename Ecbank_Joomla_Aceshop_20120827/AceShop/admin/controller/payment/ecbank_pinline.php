<?php 
class ControllerPaymentEcbankPinLine extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('payment/ecbank_pinline');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ecbank_pinline', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		
		$this->data['entry_bank'] = $this->language->get('entry_bank');
		
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');
		
		$this->data['entry_account'] = $this->language->get('entry_account');
		$this->data['entry_checkcode'] = $this->language->get('entry_checkcode');
                $this->data['entry_i_invoice'] = $this->language->get('entry_i_invoice');
		$this->data['entry_i_invoice_yes'] = $this->language->get('entry_i_invoice_yes');                
                $this->data['entry_i_invoice_no'] = $this->language->get('entry_i_invoice_no');
                $this->data['entry_imer_id'] = $this->language->get('entry_imer_id');
                $this->data['entry_delay'] = $this->language->get('entry_delay');                

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
 		if (isset($this->error['warning2'])) {
			$this->data['error_warning2'] = $this->error['warning2'];
		} else {
			$this->data['error_warning2'] = '';
		}
		
 		if (isset($this->error['warning3'])) {
			$this->data['error_warning3'] = $this->error['warning3'];
		} else {
			$this->data['error_warning3'] = '';
		}
		
		$this->load->model('localisation/language');
		
		$languages = $this->model_localisation_language->getLanguages();
		
		foreach ($languages as $language) {
			if (isset($this->error['bank_' . $language['language_id']])) {
				$this->data['error_bank_' . $language['language_id']] = $this->error['bank_' . $language['language_id']];
			} else {
				$this->data['error_bank_' . $language['language_id']] = '';
			}
		}
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/ecbank_pinline', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/ecbank_pinline', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('localisation/language');		

		foreach ($languages as $language) {
			if (isset($this->request->post['ecbank_pinline_description_' . $language['language_id']])) {
				$this->data['ecbank_pinline_description_' . $language['language_id']] = $this->request->post['ecbank_pinline_description_' . $language['language_id']];
			} else {
				$this->data['ecbank_pinline_description_' . $language['language_id']] = $this->config->get('ecbank_pinline_description_' . $language['language_id']);
			}
		}
		
		$this->data['languages'] = $languages;		
	
		if (isset($this->request->post['ecbank_pinline_order_status_id'])) {
			$this->data['ecbank_pinline_order_status_id'] = $this->request->post['ecbank_pinline_order_status_id'];
		} else {
			$this->data['ecbank_pinline_order_status_id'] = $this->config->get('ecbank_pinline_order_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['ecbank_pinline_geo_zone_id'])) {
			$this->data['ecbank_pinline_geo_zone_id'] = $this->request->post['ecbank_pinline_geo_zone_id'];
		} else {
			$this->data['ecbank_pinline_geo_zone_id'] = $this->config->get('ecbank_pinline_geo_zone_id'); 
		} 
		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['ecbank_pinline_status'])) {
			$this->data['ecbank_pinline_status'] = $this->request->post['ecbank_pinline_status'];
		} else {
			$this->data['ecbank_pinline_status'] = $this->config->get('ecbank_pinline_status');
		}
		
		if (isset($this->request->post['ecbank_pinline_sort_order'])) {
			$this->data['ecbank_pinline_sort_order'] = $this->request->post['ecbank_pinline_sort_order'];
		} else {
			$this->data['ecbank_pinline_sort_order'] = $this->config->get('ecbank_pinline_sort_order');
		}

		if (isset($this->request->post['ecbank_pinline_account'])) {
			$this->data['ecbank_pinline_account'] = $this->request->post['ecbank_pinline_account'];
		} else {
			$this->data['ecbank_pinline_account'] = $this->config->get('ecbank_pinline_account');
		}

		if (isset($this->request->post['ecbank_pinline_checkcode'])) {
			$this->data['ecbank_pinline_checkcode'] = $this->request->post['ecbank_pinline_checkcode'];
		} else {
			$this->data['ecbank_pinline_checkcode'] = $this->config->get('ecbank_pinline_checkcode');
		}
                if (isset($this->request->post['ecbank_pinline_i_invoice'])) {
			$this->data['ecbank_pinline_i_invoice'] = $this->request->post['ecbank_pinline_i_invoice'];
		} else {
			$this->data['ecbank_pinline_i_invoice'] = $this->config->get('ecbank_pinline_i_invoice');
		}
                
                if (isset($this->request->post['ecbank_pinline_imer_id'])) {
			$this->data['ecbank_pinline_imer_id'] = $this->request->post['ecbank_pinline_imer_id'];
		} else {
			$this->data['ecbank_pinline_imer_id'] = $this->config->get('ecbank_pinline_imer_id');
		}
                
                if (isset($this->request->post['ecbank_pinline_delay'])) {
			$this->data['ecbank_pinline_delay'] = $this->request->post['ecbank_pinline_delay'];
		} else {
			$this->data['ecbank_pinline_delay'] = $this->config->get('ecbank_pinline_delay');
		}                 

		$this->template = 'payment/ecbank_pinline.tpl';
		$this->children = array(
			'common/header',	
			'common/footer',
		);
		
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/ecbank_pinline')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['ecbank_pinline_account']) {
			$this->error['warning2'] = $this->language->get('error_account');
		}
		
		if (!$this->request->post['ecbank_pinline_checkcode']) {
			$this->error['warning3'] = $this->language->get('error_checkcode');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>