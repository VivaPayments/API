<?php
class ControllerExtensionPaymentVivawallet extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/vivawallet');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		//OC3 + payment_
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			
			$this->model_setting_setting->editSetting('payment_vivawallet', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			//OC3 extension -> marketplace, token -> user_token, SSL -> true, type=payment, variables + payment_, remove .tpl
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_authorize'] = $this->language->get('text_authorize');
		$data['text_sale'] = $this->language->get('text_sale');
		$data['text_instalments'] = $this->language->get('text_instalments');
		
		$data['text_webcheckout'] = $this->language->get('text_webcheckout');
		$data['text_receipt'] = $this->language->get('text_receipt');
		$data['text_checkoutreceipt'] = $this->language->get('text_checkoutreceipt');

		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_successful'] = $this->language->get('text_successful');
		$data['text_declined'] = $this->language->get('text_declined');
		$data['text_off'] = $this->language->get('text_off');

		$data['entry_total'] = $this->language->get('entry_total');
		$data['help_total'] = $this->language->get('help_total');
		$data['entry_merchantid'] = $this->language->get('entry_merchantid');
		$data['entry_merchantpass'] = $this->language->get('entry_merchantpass');
		$data['entry_maxinstal'] = $this->language->get('entry_maxinstal');
		$data['entry_source'] = $this->language->get('entry_source');
		$data['entry_orderurl'] = $this->language->get('entry_orderurl');
		$data['entry_url'] = $this->language->get('entry_url');	

		$data['entry_processed_status'] = $this->language->get('entry_processed_status');		
		$data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchantid'])) {
			$data['error_merchantid'] = $this->error['merchantid'];
		} else {
			$data['error_merchantid'] = '';
		}

		if (isset($this->error['merchantpass'])) {
			$data['error_merchantpass'] = $this->error['merchantpass'];
		} else {
			$data['error_merchantpass'] = '';
		}
		
		if (isset($this->error['orderurl'])) {
			$data['error_orderurl'] = $this->error['orderurl'];
		} else {
			$data['error_orderurl'] = '';
		}
		
		if (isset($this->error['username'])) {
			$data['error_username'] = $this->error['username'];
		} else {
			$data['error_username'] = '';
		}
		
		if (isset($this->error['url'])) {
			$data['error_url'] = $this->error['url'];
		} else {
			$data['error_url'] = '';
		}								
		
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/vivawallet', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/vivawallet', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->request->post['payment_vivawallet_total'])) {
			$data['payment_vivawallet_total'] = $this->request->post['payment_vivawallet_total'];
		} else {
			$data['payment_vivawallet_total'] = $this->config->get('payment_vivawallet_total'); 
		}
		
		if (isset($this->request->post['payment_vivawallet_merchantid'])) {
			$data['payment_vivawallet_merchantid'] = $this->request->post['payment_vivawallet_merchantid'];
		} else {
			$data['payment_vivawallet_merchantid'] = $this->config->get('payment_vivawallet_merchantid');
		}
		
		if (isset($this->request->post['payment_vivawallet_merchantpass'])) {
			$data['payment_vivawallet_merchantpass'] = $this->request->post['payment_vivawallet_merchantpass'];
		} else {
			$data['payment_vivawallet_merchantpass'] = $this->config->get('payment_vivawallet_merchantpass');
		}
		
		if (isset($this->request->post['payment_vivawallet_maxinstal'])) {
			$data['payment_vivawallet_maxinstal'] = $this->request->post['payment_vivawallet_maxinstal'];
		} else {
			$data['payment_vivawallet_maxinstal'] = $this->config->get('payment_vivawallet_maxinstal');
		}
		
		if (isset($this->request->post['payment_vivawallet_source'])) {
			$data['payment_vivawallet_source'] = $this->request->post['payment_vivawallet_source'];
		} else {
			$data['payment_vivawallet_source'] = $this->config->get('payment_vivawallet_source');
		}

		if (isset($this->request->post['payment_vivawallet_orderurl'])) {
			$data['payment_vivawallet_orderurl'] = $this->request->post['payment_vivawallet_orderurl'];
		} else {
			$data['payment_vivawallet_orderurl'] = $this->config->get('payment_vivawallet_orderurl');
		}
		
		if (!$data['payment_vivawallet_orderurl']) {
			$data['payment_vivawallet_orderurl'] = 'https://www.vivapayments.com/api/orders';
		}
		
		if (isset($this->request->post['payment_vivawallet_url'])) {
			$data['payment_vivawallet_url'] = $this->request->post['payment_vivawallet_url'];
		} else {
			$data['payment_vivawallet_url'] = $this->config->get('payment_vivawallet_url');
		}
		
		if (!$data['payment_vivawallet_url']) {
			$data['payment_vivawallet_url'] = 'https://www.vivapayments.com/web/newtransaction.aspx';
		}	
		
		if (isset($this->request->post['payment_vivawallet_processed_status_id'])) {
			$data['payment_vivawallet_processed_status_id'] = $this->request->post['payment_vivawallet_processed_status_id'];
		} else {
			$data['payment_vivawallet_processed_status_id'] = $this->config->get('payment_vivawallet_processed_status_id');
		}
		if (!$data['payment_vivawallet_processed_status_id']) $data['payment_vivawallet_processed_status_id'] = 15;  # "Processed"

		if (isset($this->request->post['payment_vivawallet_failed_status_id'])) {
			$data['payment_vivawallet_failed_status_id'] = $this->request->post['payment_vivawallet_failed_status_id'];
		} else {
			$data['payment_vivawallet_failed_status_id'] = $this->config->get('payment_vivawallet_failed_status_id');
		}
		if (!$data['payment_vivawallet_failed_status_id']) $data['payment_vivawallet_failed_status_id'] = 10;  # "Failed"

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_vivawallet_geo_zone_id'])) {
			$data['payment_vivawallet_geo_zone_id'] = $this->request->post['payment_vivawallet_geo_zone_id'];
		} else {
			$data['payment_vivawallet_geo_zone_id'] = $this->config->get('payment_vivawallet_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_vivawallet_status'])) {
			$data['payment_vivawallet_status'] = $this->request->post['payment_vivawallet_status'];
		} else {
			$data['payment_vivawallet_status'] = $this->config->get('payment_vivawallet_status');
		}

		if (isset($this->request->post['payment_vivawallet_sort_order'])) {
			$data['payment_vivawallet_sort_order'] = $this->request->post['payment_vivawallet_sort_order'];
		} else {
			$data['payment_vivawallet_sort_order'] = $this->config->get('payment_vivawallet_sort_order');
		}

		  $this->db->query("CREATE TABLE IF NOT EXISTS oc_vivawallet_data (
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

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/vivawallet', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/vivawallet')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_vivawallet_merchantid']) {
			$this->error['merchantid'] = $this->language->get('error_merchantid');
		}
		
		if (!$this->request->post['payment_vivawallet_merchantpass']) {
			$this->error['merchantpass'] = $this->language->get('error_merchantpass');
		}
		
		if (!$this->request->post['payment_vivawallet_orderurl']) {
			$this->error['orderurl'] = $this->language->get('error_orderurl');
		}
		
		if (!$this->request->post['payment_vivawallet_url']) {
			$this->error['url'] = $this->language->get('error_url');
		}
		
		return !$this->error;
	}
}