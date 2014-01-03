<?php 
class ControllerPaymentEcbankWebatm extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('payment/ecbank_webatm');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ecbank_webatm', $this->request->post);				
			
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
		//$this->data['entry_bank_type'] = $this->language->get('entry_bank_type');
                
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
			'href'      => $this->url->link('payment/ecbank_webatm', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/ecbank_webatm', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('localisation/language');		

		foreach ($languages as $language) {
			if (isset($this->request->post['ecbank_webatm_description_' . $language['language_id']])) {
				$this->data['ecbank_webatm_description_' . $language['language_id']] = $this->request->post['ecbank_webatm_description_' . $language['language_id']];
			} else {
				$this->data['ecbank_webatm_description_' . $language['language_id']] = $this->config->get('ecbank_webatm_description_' . $language['language_id']);
			}
		}
		
		$this->data['languages'] = $languages;
	
		if (isset($this->request->post['ecbank_webatm_order_status_id'])) {
			$this->data['ecbank_webatm_order_status_id'] = $this->request->post['ecbank_webatm_order_status_id'];
		} else {
			$this->data['ecbank_webatm_order_status_id'] = $this->config->get('ecbank_webatm_order_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['ecbank_webatm_geo_zone_id'])) {
			$this->data['ecbank_webatm_geo_zone_id'] = $this->request->post['ecbank_webatm_geo_zone_id'];
		} else {
			$this->data['ecbank_webatm_geo_zone_id'] = $this->config->get('ecbank_webatm_geo_zone_id'); 
		} 
		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['ecbank_webatm_status'])) {
			$this->data['ecbank_webatm_status'] = $this->request->post['ecbank_webatm_status'];
		} else {
			$this->data['ecbank_webatm_status'] = $this->config->get('ecbank_webatm_status');
		}
		
		if (isset($this->request->post['ecbank_webatm_sort_order'])) {
			$this->data['ecbank_webatm_sort_order'] = $this->request->post['ecbank_webatm_sort_order'];
		} else {
			$this->data['ecbank_webatm_sort_order'] = $this->config->get('ecbank_webatm_sort_order');
		}

		if (isset($this->request->post['ecbank_webatm_account'])) {
			$this->data['ecbank_webatm_account'] = $this->request->post['ecbank_webatm_account'];
		} else {
			$this->data['ecbank_webatm_account'] = $this->config->get('ecbank_webatm_account');
		}

		if (isset($this->request->post['ecbank_webatm_checkcode'])) {
			$this->data['ecbank_webatm_checkcode'] = $this->request->post['ecbank_webatm_checkcode'];
		} else {
			$this->data['ecbank_webatm_checkcode'] = $this->config->get('ecbank_webatm_checkcode');
		}
		if (isset($this->request->post['ecbank_webatm_i_invoice'])) {
			$this->data['ecbank_webatm_i_invoice'] = $this->request->post['ecbank_webatm_i_invoice'];
		} else {
			$this->data['ecbank_webatm_i_invoice'] = $this->config->get('ecbank_webatm_i_invoice');
		}
                
                if (isset($this->request->post['ecbank_webatm_imer_id'])) {
			$this->data['ecbank_webatm_imer_id'] = $this->request->post['ecbank_webatm_imer_id'];
		} else {
			$this->data['ecbank_webatm_imer_id'] = $this->config->get('ecbank_webatm_imer_id');
		}
                
                if (isset($this->request->post['ecbank_webatm_delay'])) {
			$this->data['ecbank_webatm_delay'] = $this->request->post['ecbank_webatm_delay'];
		} else {
			$this->data['ecbank_webatm_delay'] = $this->config->get('ecbank_webatm_delay');
		} 
/*		unset($bank_types);
		$bank_types = Array();
		for($i=0;$i<=1;$i++){
			$bank_types[$i]['code'] = $this->language->get('entry_bank_type_code_range['.$i.']');
			$bank_types[$i]['name'] = $this->language->get('entry_bank_type_name_range['.$i.']');
		}
		$this->data['bank_types'] = $bank_types;
		
		if (isset($this->request->post['ecbank_webatm_bank_type'])) {
			$this->data['ecbank_webatm_bank_type'] = $this->request->post['ecbank_webatm_bank_type'];
		} else {
			$this->data['ecbank_webatm_bank_type'] = $this->config->get('ecbank_webatm_bank_type'); 
		}
*/
		$this->template = 'payment/ecbank_webatm.tpl';
		$this->children = array(
			'common/header',	
			'common/footer',
		);
		
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/ecbank_webatm')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['ecbank_webatm_account']) {
			$this->error['warning2'] = $this->language->get('error_account');
		}
		
		if (!$this->request->post['ecbank_webatm_checkcode']) {
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