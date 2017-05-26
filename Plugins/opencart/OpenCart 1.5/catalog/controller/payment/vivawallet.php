<?php
ini_set("display_errors", 0);
class ControllerPaymentVivawallet extends Controller {
	protected function index() {
	$this->data['button_confirm'] = $this->language->get('button_confirm');

	$this->load->model('checkout/order');
		
	$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
	$vivawallet_merchantreference = 'REF'.substr(md5(uniqid(rand(), true)), 0, 9);
	$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	$vivawallet_total_eur = $this->currency->format($order_info['total'], 'EUR', '',false);
	$vivawallet_total_cents = round($vivawallet_total_eur * 100);
	$vivawallet_orderid = $this->session->data['order_id'];
	
	if(strtoupper($this->language->get('code')) == 'GR' || strtoupper($this->language->get('code')) == 'EL'){
		$vivawallet_language = 'el-GR';
		} else {
		$vivawallet_language = 'en-US';
	}
			
    $MerchantID = $this->config->get('vivawallet_merchantid');
	$Password =  html_entity_decode($this->config->get('vivawallet_merchantpass'));
	
	$poststring['Amount'] = $vivawallet_total_cents;
	$poststring['RequestLang'] = $vivawallet_language;
	
	$poststring['Email'] = $order_info['email'];
	
	$vivawallet_total_eur = $this->currency->format($order_info['total'], 'EUR', '',false);
	$charge = number_format($vivawallet_total_eur, '2', '.', '');
	$maxperiod = '';
	 $installogic = $this->config->get('vivawallet_maxinstal');
	 if(isset($installogic) && $installogic!=''){
	 $split_instal_vivawallet = explode(',',$installogic);
	 $c = count($split_instal_vivawallet);	
	 $instal_vivawallet_max = array();
	 for($i=0; $i<$c; $i++){
		list($instal_amount, $instal_term) = explode(":", $split_instal_vivawallet[$i]);
		if($charge >= $instal_amount){
		$instal_vivawallet_max[] = trim($instal_term);
		}
	}
		if(count($instal_vivawallet_max) > 0){
		 $maxperiod = max($instal_vivawallet_max);
		} 
	}
	
	$poststring['MerchantTrns'] = $vivawallet_orderid;
	$poststring['SourceCode'] = $this->config->get('vivawallet_source');
	$poststring['PaymentTimeOut'] = '300';
	
	$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($order_info['email']);
	
	if(isset($maxperiod) && $maxperiod > 0){ 
	$postargs .= '&MaxInstallments='.$maxperiod;
	} else {
	$postargs .= '&MaxInstallments=1';
	}
	
	$postargs .= '&MerchantTrns='.urlencode($poststring['MerchantTrns']);
	$postargs .= '&SourceCode='.urlencode($poststring['SourceCode']);
	$postargs .= '&PaymentTimeOut=300';

	$curl = curl_init($this->config->get('vivawallet_orderurl'));
	
	if (preg_match("/https/i", $this->config->get('vivawallet_orderurl'))) {
	curl_setopt($curl, CURLOPT_PORT, 443);
	} 

	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
	$curlversion = curl_version();
	if(!preg_match("/NSS/" , $curlversion['ssl_version'])){
	curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, "TLSv1");
	}
	
	// execute curl
	$response = curl_exec($curl);
		
	if(curl_error($curl)){
	curl_setopt($curl, CURLOPT_PORT, 443);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($curl);
	}
	
		curl_close($curl);
		
		try {
		if (version_compare(PHP_VERSION, '5.3.99', '>=')) {
		$resultObj=json_decode($response, false, 512, JSON_BIGINT_AS_STRING);
		} else {
		$response = preg_replace('/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/', ': "$1"', $response, 1);
		$resultObj = json_decode($response);
		}
		} catch( Exception $e ) {
			throw new Exception("Result is not a json object (" . $e->getMessage() . ")");
		}
		
		if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
		$OrderCode = $resultObj->OrderCode;
		$ErrorCode = $resultObj->ErrorCode;
		$ErrorText = $resultObj->ErrorText;
		}
		else{
			throw new Exception("Unable to create order code (" . $resultObj->ErrorText . ")");
		}	

	$this->db->query("insert into oc_vivawallet_data (OrderCode, ErrorCode, ErrorText, Timestamp, ref, total_cost, currency, order_state) values ('".$OrderCode."','".$ErrorCode."','".$ErrorText."',now(),'".$vivawallet_orderid."','".$vivawallet_total_cents."','978','I')");	
		

		$this->data['action'] = $this->config->get('vivawallet_url');
		$this->data['vivawallet_ordercode'] = $OrderCode;
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/vivawallet.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/vivawallet.tpl';
		} else {
			$this->template = 'default/template/payment/vivawallet.tpl';
		}		
		
		$this->render();
	}
	
public function callback() {

//$this->load->language('payment/vivawallet');
$this->language->load('payment/vivawallet');

$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
	$this->data['base'] = $this->config->get('config_url');
} else {
	$this->data['base'] = $this->config->get('config_ssl');
}

$this->data['language'] = $this->language->get('code');
$this->data['direction'] = $this->language->get('direction');
$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
$this->data['text_failure'] = $this->language->get('text_failure');
$this->data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/checkout', '', 'SSL'));
	  	  
	  //fail
	  if(preg_match("/fail/i", $_SERVER['REQUEST_URI'])) {
	  $tm_ref = $_GET['s'];
	  
	  if(isset($tm_ref) && $tm_ref !='') {
	  
	  $check_query = $this->db->query("select ref from oc_vivawallet_data where OrderCode='".$this->db->escape($tm_ref)."'");
		
		if($check_query->rows){
		$oRecord = $check_query->rows[0];
  
        $this->db->query("update oc_vivawallet_data set order_state='F' where OrderCode='".$this->db->escape($tm_ref)."'");
		
		}
	  
	  }

		$this->data['continue'] = $this->url->link('checkout/cart');
	
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/vivawallet_failure.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/vivawallet_failure.tpl';
		} else {
			$this->template = 'default/template/payment/vivawallet_failure.tpl';
		}
		
		$this->response->setOutput($this->render());
	  
	  
	  }//end fail
	  
	  //success
	  elseif(preg_match("/success/i", $_SERVER['REQUEST_URI'])) {
	  
	  $tm_ref = $_GET['s'];
	  
	  if(isset($tm_ref) && $tm_ref !='') {
	  
	  	$check_query = $this->db->query("select ref from oc_vivawallet_data where OrderCode='".$this->db->escape($tm_ref)."'");
		
		if($check_query->rows){
		$oRecord = $check_query->rows[0];
	  
		$this->db->query("update oc_vivawallet_data set order_state='P' where OrderCode='".$this->db->escape($tm_ref)."'");
  
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($oRecord['ref']);
		$this->model_checkout_order->confirm($oRecord['ref'], $this->config->get('vivawallet_processed_status_id'));
		$this->redirect($this->url->link('checkout/success'));
  		}
       } 
	  }//end success	 
	  
	  

	}//end callback
}//end class
?>