<?php 
class ControllerPaymentGwEcpayeng extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('payment/gw_ecpayeng');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('gw_ecpayeng', $this->request->post);				
			
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
		$this->data['entry_cfg_value'] = $this->language->get('entry_cfg_value');
		$this->data['entry_cfg_tip'] = $this->language->get('entry_cfg_tip');
		$this->data['entry_stagemethod'] = $this->language->get('entry_stagemethod');
		$this->data['entry_stagemethod_contain'] = $this->language->get('entry_stagemethod_contain');
		$this->data['entry_stagemethod_add'] = $this->language->get('entry_stagemethod_add');
		$this->data['entry_stagemethod_tip'] = $this->language->get('entry_stagemethod_tip');                
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
			'href'      => $this->url->link('payment/gw_ecpayeng', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/gw_ecpayeng', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('localisation/language');	
		
		foreach ($languages as $language) {
			if (isset($this->request->post['gw_ecpayeng_description_' . $language['language_id']])) {
				$this->data['gw_ecpayeng_description_' . $language['language_id']] = $this->request->post['gw_ecpayeng_description_' . $language['language_id']];
			} else {
				$this->data['gw_ecpayeng_description_' . $language['language_id']] = $this->config->get('gw_ecpayeng_description_' . $language['language_id']);
			}
		}
		
		$this->data['languages'] = $languages;
		
		if (isset($this->request->post['gw_ecpayeng_order_status_id'])) {
			$this->data['gw_ecpayeng_order_status_id'] = $this->request->post['gw_ecpayeng_order_status_id'];
		} else {
			$this->data['gw_ecpayeng_order_status_id'] = $this->config->get('gw_ecpayeng_order_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['gw_ecpayeng_geo_zone_id'])) {
			$this->data['gw_ecpayeng_geo_zone_id'] = $this->request->post['gw_ecpayeng_geo_zone_id'];
		} else {
			$this->data['gw_ecpayeng_geo_zone_id'] = $this->config->get('gw_ecpayeng_geo_zone_id'); 
		} 
		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['gw_ecpayeng_status'])) {
			$this->data['gw_ecpayeng_status'] = $this->request->post['gw_ecpayeng_status'];
		} else {
			$this->data['gw_ecpayeng_status'] = $this->config->get('gw_ecpayeng_status');
		}
		
		if (isset($this->request->post['gw_ecpayeng_sort_order'])) {
			$this->data['gw_ecpayeng_sort_order'] = $this->request->post['gw_ecpayeng_sort_order'];
		} else {
			$this->data['gw_ecpayeng_sort_order'] = $this->config->get('gw_ecpayeng_sort_order');
		}

		if (isset($this->request->post['gw_ecpayeng_account'])) {
			$this->data['gw_ecpayeng_account'] = $this->request->post['gw_ecpayeng_account'];
		} else {
			$this->data['gw_ecpayeng_account'] = $this->config->get('gw_ecpayeng_account');
		}

		if (isset($this->request->post['gw_ecpayeng_checkcode'])) {
			$this->data['gw_ecpayeng_checkcode'] = $this->request->post['gw_ecpayeng_checkcode'];
		} else {
			$this->data['gw_ecpayeng_checkcode'] = $this->config->get('gw_ecpayeng_checkcode');
		}
                if (isset($this->request->post['gw_ecpayeng_i_invoice'])) {
			$this->data['gw_ecpayeng_i_invoice'] = $this->request->post['gw_ecpayeng_i_invoice'];
		} else {
			$this->data['gw_ecpayeng_i_invoice'] = $this->config->get('gw_ecpayeng_i_invoice');
		}
                
                if (isset($this->request->post['gw_ecpayeng_imer_id'])) {
			$this->data['gw_ecpayeng_imer_id'] = $this->request->post['gw_ecpayeng_imer_id'];
		} else {
			$this->data['gw_ecpayeng_imer_id'] = $this->config->get('gw_ecpayeng_imer_id');
		}
                
                if (isset($this->request->post['gw_ecpayeng_delay'])) {
			$this->data['gw_ecpayeng_delay'] = $this->request->post['gw_ecpayeng_delay'];
		} else {
			$this->data['gw_ecpayeng_delay'] = $this->config->get('gw_ecpayeng_delay');
		}     
		if (isset($this->request->post['gw_ecpayeng_cfg_value'])) {
			$this->data['gw_ecpayeng_cfg_value'] = $this->request->post['gw_ecpayeng_cfg_value'];
		} else {
			$this->data['gw_ecpayeng_cfg_value'] = $this->config->get('gw_ecpayeng_cfg_value');
		}
		if(isset($this->request->post['gw_ecpayeng_stagemethod'])){
			$this->data['gw_ecpayeng_stagemethod'] = $this->request->post['gw_ecpayeng_stagemethod'];
		}else{
			$this->data['gw_ecpayeng_stagemethod'] = $this->config->get('gw_ecpayeng_stagemethod');
		}                

		$this->template = 'payment/gw_ecpayeng.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/gw_ecpayeng')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['gw_ecpayeng_account']) {
			$this->error['warning2'] = $this->language->get('error_account');
		}
		
		if (!$this->request->post['gw_ecpayeng_checkcode']) {
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