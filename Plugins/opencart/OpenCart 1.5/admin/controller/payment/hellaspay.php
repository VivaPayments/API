<?php

class ControllerPaymentHellaspay extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('payment/hellaspay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			
			$this->model_setting_setting->editSetting('hellaspay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_authorize'] = $this->language->get('text_authorize');
		$this->data['text_sale'] = $this->language->get('text_sale');
		$this->data['text_instalments'] = $this->language->get('text_instalments');
		
		$this->data['text_webcheckout'] = $this->language->get('text_webcheckout');
		$this->data['text_receipt'] = $this->language->get('text_receipt');
		$this->data['text_checkoutreceipt'] = $this->language->get('text_checkoutreceipt');

		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_successful'] = $this->language->get('text_successful');
		$this->data['text_declined'] = $this->language->get('text_declined');
		$this->data['text_off'] = $this->language->get('text_off');

		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_merchantid'] = $this->language->get('entry_merchantid');
		$this->data['entry_merchantpass'] = $this->language->get('entry_merchantpass');
		$this->data['entry_maxinstal'] = $this->language->get('entry_maxinstal');
		$this->data['entry_source'] = $this->language->get('entry_source');
		$this->data['entry_orderurl'] = $this->language->get('entry_orderurl');
		$this->data['entry_url'] = $this->language->get('entry_url');	

		$this->data['entry_processed_status'] = $this->language->get('entry_processed_status');		
		$this->data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['merchantid'])) {
			$this->data['error_merchantid'] = $this->error['merchantid'];
		} else {
			$this->data['error_merchantid'] = '';
		}

		if (isset($this->error['merchantpass'])) {
			$this->data['error_merchantpass'] = $this->error['merchantpass'];
		} else {
			$this->data['error_merchantpass'] = '';
		}
		
		if (isset($this->error['orderurl'])) {
			$this->data['error_orderurl'] = $this->error['orderurl'];
		} else {
			$this->data['error_orderurl'] = '';
		}
		
		if (isset($this->error['username'])) {
			$this->data['error_username'] = $this->error['username'];
		} else {
			$this->data['error_username'] = '';
		}
		
		if (isset($this->error['url'])) {
			$this->data['error_url'] = $this->error['url'];
		} else {
			$this->data['error_url'] = '';
		}								
		
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'		=> $this->language->get('text_home'),
			'href'		=> $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => FALSE
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/hellaspay', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/hellaspay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['hellaspay_total'])) {
			$this->data['hellaspay_total'] = $this->request->post['hellaspay_total'];
		} else {
			$this->data['hellaspay_total'] = $this->config->get('hellaspay_total'); 
		}
		
		if (isset($this->request->post['hellaspay_merchantid'])) {
			$this->data['hellaspay_merchantid'] = $this->request->post['hellaspay_merchantid'];
		} else {
			$this->data['hellaspay_merchantid'] = $this->config->get('hellaspay_merchantid');
		}
		
		if (isset($this->request->post['hellaspay_merchantpass'])) {
			$this->data['hellaspay_merchantpass'] = $this->request->post['hellaspay_merchantpass'];
		} else {
			$this->data['hellaspay_merchantpass'] = $this->config->get('hellaspay_merchantpass');
		}
		
		if (isset($this->request->post['hellaspay_maxinstal'])) {
			$this->data['hellaspay_maxinstal'] = $this->request->post['hellaspay_maxinstal'];
		} else {
			$this->data['hellaspay_maxinstal'] = $this->config->get('hellaspay_maxinstal');
		}
		
		if (isset($this->request->post['hellaspay_source'])) {
			$this->data['hellaspay_source'] = $this->request->post['hellaspay_source'];
		} else {
			$this->data['hellaspay_source'] = $this->config->get('hellaspay_source');
		}

		if (isset($this->request->post['hellaspay_orderurl'])) {
			$this->data['hellaspay_orderurl'] = $this->request->post['hellaspay_orderurl'];
		} else {
			$this->data['hellaspay_orderurl'] = $this->config->get('hellaspay_orderurl');
		}
		
		if ($this->data['hellaspay_orderurl'] == '') {
			$this->data['hellaspay_orderurl'] = 'https://www.vivapayments.com/api/orders';
		}
		
		if (isset($this->request->post['hellaspay_url'])) {
			$this->data['hellaspay_url'] = $this->request->post['hellaspay_url'];
		} else {
			$this->data['hellaspay_url'] = $this->config->get('hellaspay_url');
		}
		
		if ($this->data['hellaspay_url'] == '') {
			$this->data['hellaspay_url'] = 'https://www.vivapayments.com/web/newtransaction.aspx';
		}	
		
		if (isset($this->request->post['hellaspay_processed_status_id'])) {
			$this->data['hellaspay_processed_status_id'] = $this->request->post['hellaspay_processed_status_id'];
		} else {
			$this->data['hellaspay_processed_status_id'] = $this->config->get('hellaspay_processed_status_id');
		}
		if ( ! $this->data['hellaspay_processed_status_id']) $this->data['hellaspay_processed_status_id'] = 15;  # "Processed"

		if (isset($this->request->post['hellaspay_failed_status_id'])) {
			$this->data['hellaspay_failed_status_id'] = $this->request->post['hellaspay_failed_status_id'];
		} else {
			$this->data['hellaspay_failed_status_id'] = $this->config->get('hellaspay_failed_status_id');
		}
		if ( ! $this->data['hellaspay_failed_status_id']) $this->data['hellaspay_failed_status_id'] = 10;  # "Failed"

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['hellaspay_geo_zone_id'])) {
			$this->data['hellaspay_geo_zone_id'] = $this->request->post['hellaspay_geo_zone_id'];
		} else {
			$this->data['hellaspay_geo_zone_id'] = $this->config->get('hellaspay_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['hellaspay_status'])) {
			$this->data['hellaspay_status'] = $this->request->post['hellaspay_status'];
		} else {
			$this->data['hellaspay_status'] = $this->config->get('hellaspay_status');
		}

		if (isset($this->request->post['hellaspay_sort_order'])) {
			$this->data['hellaspay_sort_order'] = $this->request->post['hellaspay_sort_order'];
		} else {
			$this->data['hellaspay_sort_order'] = $this->config->get('hellaspay_sort_order');
		}

		$this->template = 'payment/hellaspay.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);


  $this->db->query("CREATE TABLE IF NOT EXISTS oc_hellaspay_data (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  OrderCode varchar(255) DEFAULT NULL,
  ErrorCode varchar(50) DEFAULT NULL,
  ErrorText varchar(255) DEFAULT NULL,
  Timestamp datetime DEFAULT NULL,
  ref varchar(150) DEFAULT NULL,
  total_cost int(11) DEFAULT NULL,
  currency char(3) DEFAULT NULL,
  order_state char(1) DEFAULT NULL,
  sessionid varchar(50) DEFAULT NULL,
  PRIMARY KEY (id))");   
  $this->response->setOutput($this->render());
	}

	private function validate() {
		if ( ! $this->user->hasPermission('modify', 'payment/hellaspay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ( ! $this->request->post['hellaspay_merchantid']) {
			$this->error['merchantid'] = $this->language->get('error_merchantid');
		}
		
		if ( ! $this->request->post['hellaspay_merchantpass']) {
			$this->error['merchantpass'] = $this->language->get('error_merchantpass');
		}
		
		if ( ! $this->request->post['hellaspay_orderurl']) {
			$this->error['orderurl'] = $this->language->get('error_orderurl');
		}
		
		if ( ! $this->request->post['hellaspay_url']) {
			$this->error['url'] = $this->language->get('hellaspay_url');
		}
		
		if ( ! $this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}

?>