<?php
ini_set("display_errors", 0);
class ControllerPaymentHellaspay extends Controller {
	protected function index() {
	
	$this->language->load('payment/hellaspay');
	$this->data['button_confirm'] = $this->language->get('button_confirm');
	$this->data['button_back'] = $this->language->get('button_back');

	$this->load->model('checkout/order');
		
	$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
	$hellaspay_merchantreference = 'REF'.substr(md5(uniqid(rand(), true)), 0, 9);
	$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	$hellaspay_total_eur = $this->currency->format($order_info['total'], 'EUR', '',false);
	$hellaspay_total_cents = round($hellaspay_total_eur * 100);
	$hellaspay_orderid = $this->session->data['order_id'];
	
	if(strtoupper($this->language->get('code')) == 'GR' || strtoupper($this->language->get('code')) == 'EL'){
		$hellaspay_language = 'el-GR';
		} else {
		$hellaspay_language = 'en-US';
	}
			
    $MerchantID = trim($this->config->get('hellaspay_merchantid'));
	$Password =  trim(html_entity_decode($this->config->get('hellaspay_merchantpass')));
	
	$poststring['Amount'] = $hellaspay_total_cents;
	$poststring['RequestLang'] = $hellaspay_language;
	
	$poststring['Email'] = $order_info['email'];
	$maxinastal = $this->config->get('hellaspay_maxinstal');
	if(isset($maxinastal) && $maxinastal > 0){ 
	$poststring['MaxInstallments'] = $maxinastal;
	}
	
	$poststring['MerchantTrns'] = $hellaspay_orderid;
	$poststring['SourceCode'] = $this->config->get('hellaspay_source');
	$poststring['PaymentTimeOut'] = '300';
	
	$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($order_info['email']);
	
	if(isset($maxinastal) && $maxinastal > 0){ 
	$postargs .= '&MaxInstallments='.$poststring['MaxInstallments'];
	}
	
	$postargs .= '&MerchantTrns='.urlencode($poststring['MerchantTrns']);
	$postargs .= '&SourceCode='.urlencode($poststring['SourceCode']);
	$postargs .= '&PaymentTimeOut=300';

	$curl = curl_init($this->config->get('hellaspay_orderurl'));
	
	if (preg_match("/https/i", $this->config->get('hellaspay_orderurl'))) {
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

	mysql_query("insert into oc_hellaspay_data (OrderCode, ErrorCode, ErrorText, Timestamp, ref, total_cost, currency, order_state) values ('".$OrderCode."','".$ErrorCode."','".$ErrorText."',now(),'".$hellaspay_orderid."','".$hellaspay_total_cents."','978','I')");	
		
$OrderCode = '123';
		$this->data['action'] = $this->config->get('hellaspay_url');
		$this->data['hellaspay_ordercode'] = $OrderCode;
		
		if ($this->request->get['route'] != 'checkout/guest_step_3') {
			$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
		} else {
			$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/guest_step_2';
		}
		
		$this->id = 'payment';
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/hellaspay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/hellaspay.tpl';
		} else {
			$this->template = 'default/template/payment/hellaspay.tpl';
		}		
		
		$this->render();
	}
	
public function callback() {

$this->language->load('payment/hellaspay');
		
$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
	$this->data['base'] = HTTP_SERVER;
} else {
	$this->data['base'] = HTTPS_SERVER;
}

$this->data['language'] = $this->language->get('code');
$this->data['direction'] = $this->language->get('direction');
$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
$this->data['text_failure'] = $this->language->get('text_failure');
$this->data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), HTTPS_SERVER . 'index.php?route=checkout/payment');
		
	  //fail
	  if(preg_match("/fail/i", $_SERVER['REQUEST_URI'])) {
	  $tm_ref = $_GET['s'];

	  if(isset($tm_ref) && $tm_ref !='') {
	  
	  $check_query = mysql_query("select ref from oc_hellaspay_data where OrderCode='".addslashes($tm_ref)."'");
		
		if(mysql_num_rows($check_query)){
	    $oRecord = mysql_fetch_assoc($check_query);
  
        mysql_query("update oc_hellaspay_data set order_state='F' where OrderCode='".$tm_ref."'");
		
	    if (isset($oRecord['ref'])) {
			$order_id = $oRecord['ref'];
		} else {
			$order_id = 0;
		}
		
		$this->data['continue'] = HTTPS_SERVER . 'index.php?route=checkout/cart';
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/hellaspay_failure.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/hellaspay_failure.tpl';
		} else {
			$this->template = 'default/template/payment/hellaspay_failure.tpl';
		}
		
		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));	
		
		}
	  
	  }
	  
	  }//end fail
	  
	  //success
	  elseif(preg_match("/success/i", $_SERVER['REQUEST_URI'])) {
	  
	  $tm_ref = $_GET['s'];
	  
	  if(isset($tm_ref) && $tm_ref !='') {
	  
	 $check_query = mysql_query("select ref from oc_hellaspay_data where OrderCode='".addslashes($tm_ref)."'");
		
		if(mysql_num_rows($check_query)){
	    $oRecord = mysql_fetch_assoc($check_query);
  
		mysql_query("update oc_hellaspay_data set order_state='P' where OrderCode='".$tm_ref."'");
  
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($oRecord['ref']);
		$this->model_checkout_order->confirm($oRecord['ref'], $this->config->get('hellaspay_processed_status_id'));
		$this->redirect(HTTP_SERVER . 'index.php?route=checkout/success');
  		}
       } 
	  }//end success	 
	  
	  

	}//end callback
}//end class
?>