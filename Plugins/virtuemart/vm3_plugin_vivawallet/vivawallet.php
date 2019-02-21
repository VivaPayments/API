<?php
//VM3 change
defined('_JEXEC') or die('Restricted access');

if (!class_exists('vmPSPlugin'))
require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class plgVmPaymentVivawallet extends vmPSPlugin {

	// instance of class
	public static $_this = false;
	private $_vivinstalments = '';
	private $_vivinstalmentsamount = '';
	private $instalmentoptions = '';
	private $instalmentcharge = '';
	private $split_instal_vivawallet = '';
	private $merchant_ref = '';
	private $vivawallet_amount = '';
	private $vivawallet_lang = '';
	private $vivawallet_period = '';
	private $vivawallet_period_note = '';
	private $tm_private_key = 'QzIwMTJfVC5DLiB2YW4gZGVyIFZlZXJfd2ViaXQuYno=';
	

//EB SET CONFIG TABLE FIELDS
	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);

		$this->_loggable = true;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id'; //virtuemart_vivawallet_id';
		$this->_tableId = 'id'; //'virtuemart_vivawallet_id';
		$varsToPush = array(
		'vivawallet_merchant_id' => array('', 'char'),
		'vivawallet_merchant_pass' => array('', 'char'),
		'vivawallet_source' => array('', 'char'),
		'vivawallet_instalments' => array('', 'char'),
		'vivawallet_instalments_charge' => array('', 'char'),
	    'vivawallet_production' => array('', 'int'),
	    'payment_currency' => array('', 'int'),
	    'payment_logos' => array('', 'char'),
		'debug' => array(0, 'int'),
	    'status_pending' => array('', 'char'),
	    'status_success' => array('', 'char'),
	    'status_canceled' => array('', 'char'),
		'language' => array('', 'char'),
	    'countries' => array('', 'char'),
	    'min_amount' => array('', 'int'),
	    'max_amount' => array('', 'int'),
	    'cost_per_transaction' => array('', 'int'),
	    'cost_percent_total' => array('', 'int'),
	    'tax_id' => array(0, 'int')
		);

		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
	}

//EB DROP CONFIG TABLE ON REINSTALL	
	public function getVmPluginDropTableSQL() {

		$db = JFactory::getDBO();
		$config = JFactory::getConfig();
		
		$table = $config->getValue('config.dbprefix').'virtuemart_payment_plg_vivawallet';
		$db->setQuery('DROP TABLE IF EXISTS ' . $table);
		return $db->query();
		
	}

//EB CREATE CONFIG TABLE	
	public function getVmPluginCreateTableSQL() {
		
		$this->getVmPluginDropTableSQL();
		return $this->createTableSQL('Payment VivaPay Table');
	}

//EB TABLE FIELDS
	function getTableSQLFields() {

		$SQLfields = array(
	    'id' => ' INT(11) unsigned NOT NULL AUTO_INCREMENT ',
	    'virtuemart_order_id' => ' int(1) UNSIGNED DEFAULT NULL',
	    'order_number' => ' char(32) DEFAULT NULL',
	    'virtuemart_paymentmethod_id' => ' mediumint(1) UNSIGNED DEFAULT NULL',
	    'payment_name' => 'varchar(5000)',
	    'payment_order_total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\' ',
	    'payment_currency' => 'char(3) ',
	    'cost_per_transaction' => ' decimal(10,2) DEFAULT NULL ',
	    'cost_percent_total' => ' decimal(10,2) DEFAULT NULL ',
	    'tax_id' => ' smallint(1) DEFAULT NULL',
	    'vivawallet_custom' => ' varchar(255)  ',
		'vivawallet_OrderCode' => ' varchar(255)  ',
		'vivawallet_ErrorCode' => ' varchar(255)  ',
		'vivawallet_ErrorText' => ' varchar(255)  ',
		'vivawallet_ref' => ' varchar(255)  ',
		'vivawallet_order_state' => ' char(1) DEFAULT NULL',
		'vivawallet_instalments' => ' varchar(5) DEFAULT NULL',
	    'vivawalletresponse_raw' => ' varchar(512) DEFAULT NULL'
		);
		return $SQLfields;
	}
	
//EB CREATE FORM DATA
	function plgVmConfirmedOrder(VirtueMartCart $cart, $order) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		$session = JFactory::getSession();
		$return_context = $session->getId();
		//$this->_debug = $method->debug;
		if (!class_exists('VirtueMartModelOrders'))
		require( VMPATH_ADMIN . DS . 'models' . DS . 'orders.php' );
		if (!class_exists('VirtueMartModelCurrency'))
		require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');

		$new_status = '';

		$usrBT = $order['details']['BT'];
		$address = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);


		if (!class_exists('TableVendors'))
		require(VMPATH_ADMIN . DS . 'table' . DS . 'vendors.php');
		$vendorModel = VmModel::getModel('Vendor');
		$vendorModel->setId(1);
		$vendor = $vendorModel->getVendor();
		$vendorModel->addImages($vendor, 1);
		$this->getPaymentCurrency($this->_currentMethod);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $this->_currentMethod->payment_currency . '" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$currency_code_3 = $db->loadResult();
		
		$currency_symbol ='';
		switch ($currency_code_3) {
		case 'EUR':
   		$currency_symbol = 978;
   		break;
		case 'GBP':
   		$currency_symbol = 826;
   		break;
		case 'BGN':
   		$currency_symbol = 975;
   		break;
		case 'RON':
   		$currency_symbol = 946;
   		break;
		default:
        $currency_symbol = 978;
		}

		$paymentCurrency = CurrencyDisplay::getInstance($this->_currentMethod->payment_currency);
		$totalInPaymentCurrency = round($paymentCurrency->convertCurrencyTo($this->_currentMethod->payment_currency, $order['details']['BT']->order_total, false), 2);
		$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);

		$merchant_id = $this->_getMerchantId($this->_currentMethod);
		if (empty($merchant_id)) {
			vmInfo(vmText::_('VMPAYMENT_VIVAWALLET_MERCHANT_ID_NOT_SET'));
			return false;
		}
		
		$merchant_pass = $this->_getMerchantPass($this->_currentMethod);
		if (empty($merchant_pass)) {
			vmInfo(vmText::_('VMPAYMENT_VIVAWALLET_MERCHANT_PASS_NOT_SET'));
			return false;
		}
		
		$merchant_ref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
		$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
		$vivawallet_amount = number_format($totalInPaymentCurrency, 2, '.', '') * 100;
		
		$lang = JFactory::getLanguage();
		$this->lang_tag = $lang->getTag();
		if(strtolower($this->lang_tag) == 'el-gr'){
		$vivawallet_lang = 'el-GR';
		} else {
		$vivawallet_lang = 'en-US';
		}
		
		$this->_getVivapayIntoSession();
		if($this->_vivinstalments > 1){
		$vivawallet_period = $this->_vivinstalments;
		$vivawallet_period_note = '- Instalments: ' . $this->_vivinstalments;
		} else {
		$vivawallet_period = '0';
		$vivawallet_period_note = '';
		}
		
		  if($vivawallet_period > 0){
		  $Installments = (int)$vivawallet_period;
		  } else {
		  $Installments = '1';
		  }
	  
		$postargs = 'Amount='.urlencode($vivawallet_amount).'&RequestLang='.urlencode($vivawallet_lang).'&Email='.urlencode($order['details']['BT']->email).'&MaxInstallments='.urlencode($Installments).'&MerchantTrns='.urlencode($order['details']['BT']->order_number).'&SourceCode='.urlencode($this->_currentMethod->vivawallet_source).'&CurrencyCode='.urlencode($currency_symbol).'&PaymentTimeOut=300';
	
		if($this->_currentMethod->vivawallet_production=='1'){
		$curl = curl_init("http://demo.vivapayments.com/api/orders");
		} else {
		$curl = curl_init("https://www.vivapayments.com/api/orders");
		curl_setopt($curl, CURLOPT_PORT, 443);
		}
		
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $this->_currentMethod->vivawallet_merchant_id.':'.html_entity_decode($this->_currentMethod->vivawallet_merchant_pass));
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
		curl_setopt($curl, CURLOPT_USERPWD, $this->_currentMethod->vivawallet_merchant_id.':'.html_entity_decode($this->_currentMethod->vivawallet_merchant_pass));
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
		
		// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = parent::renderPluginName($this->_currentMethod);
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['vivawallet_custom'] = $return_context;
		$dbValues['vivawallet_instalments'] = $Installments;
		$dbValues['vivawallet_OrderCode'] = "$OrderCode";
		$dbValues['vivawallet_ErrorCode'] = "$ErrorCode";
		$dbValues['vivawallet_ErrorText'] = "$ErrorText";
		$dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
		$dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
		$dbValues['payment_currency'] = $currency_symbol;
		$dbValues['payment_order_total'] = $totalInPaymentCurrency;
		$dbValues['tax_id'] = $this->_currentMethod->tax_id;
		$this->storePSPluginInternalData($dbValues);
		
		$post_variables = Array(
		'Ref' => $OrderCode);

		if($this->_currentMethod->vivawallet_production=='1'){
		$url = "http://demo.vivapayments.com/web/newtransaction.aspx";
		} else {
		$url = "https://www.vivapayments.com/web/newtransaction.aspx";
		}

		$html = '<html><head><title>Redirection</title></head><body><div style="margin: auto; text-align: center;">';
		$html .= '<form action="' . $url . '" method="get" name="vm_vivawallet_form" >';
		$html.= '<input type="submit"  value="' . vmText::_('VMPAYMENT_VIVAWALLET_REDIRECT_MESSAGE') . '" />';
		foreach ($post_variables as $name => $value) {
			$html.= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
		}
		$html.= '</form></div>';
		$html.= ' <script type="text/javascript">';
		$html.= ' document.vm_vivawallet_form.submit();';
		$html.= ' </script></body></html>';

		//7.1 fix
		//return $this->processConfirmedOrderPaymentResponse(2, $cart, $order, $html, $dbValues['payment_name'], $new_status);
		$this->processConfirmedOrderPaymentResponse(2, $cart, $order, $html, $dbValues['payment_name'], $new_status);
		vRequest::setVar('html', $html);

	}

//EB CURRENCY
	function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		$this->getPaymentCurrency($this->_currentMethod);
		$paymentCurrencyId = $this->_currentMethod->payment_currency;
	}

//EB AFTER ORDER PAGE
	function _getHtmlPaymentResponse($msg, $is_success=true, $order_id=null, $order_nr=null, $amount=null, $txid=null, $period=null, $date=null, $paymethodid=null) {
	
		if (!$is_success) {
			return '<p style="text-align: center;">' . vmText::_($msg) . '</p>';
		} else {
		
		$method = $this->getVmPluginMethod($paymethodid);
		$this->getPaymentCurrency($method);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$currency_code_3 = $db->loadResult();
		
		$datenew = explode(" ", $date);
		
			$html = '<table>' . "\n";
			$html .= '<thead><tr><td colspan="2" style="text-align: center;">' . vmText::_($msg) . '</td></tr></thead>';
			$html .= $this->getHtmlRow('VIVAWALLET_ORDER_NUMBER', $order_id .'/'.$order_nr, 'style="width: 90px;" class="key"');
			$html .= $this->getHtmlRow('VIVAWALLET_AMOUNT', $amount . ' ' . $currency_code_3, 'style="width: 90px;" class="key"');
			//VM3 added
            $html .= $this->getHtmlRow('VIVAWALLET_TXID', $txid, 'style="width: 90px;" class="key"');
			if(isset($period) && $period > 1){
			$html .= $this->getHtmlRow('VIVAWALLET_PERIOD', $period, 'style="width: 90px;" class="key"');
			}
			$html .= $this->getHtmlRow('VIVAWALLET_DATE', $datenew[0], 'style="width: 90px;" class="key"');
			
			$html .= '</table>' . "\n";
			$html .= '<p align="center"><a href="' . JURI::base(true) . '" title="' . vmText::_('VMPAYMENT_VIVAWALLET_HOME') . '">' . vmText::_('VMPAYMENT_VIVAWALLET_HOME') . '&raquo;</a></p>';
			
			return $html;
		}
	}
	
//EB DATA RESPONSE FAIL-SUCCESS
	function plgVmOnPaymentResponseReceived(&$html) {
	
		$tm_ref = vRequest::getString ('s', 0);
		if (!isset($tm_ref)) {
			return NULL;
		}
				
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_payment_plg_vivawallet` WHERE `vivawallet_OrderCode`="' . $tm_ref . '" ';
		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
	    	return NULL;
		}
		
		//start processing
		$virtuemart_paymentmethod_id = $paymentTable->virtuemart_paymentmethod_id;
		$virtuemart_order_id = $paymentTable->virtuemart_order_id;
		$order_number = $paymentTable->order_number;
		$vendorId = 0;
		
		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return NULL;
		}		

		if(preg_match("/bnkact=fail/i", $_SERVER['REQUEST_URI'])) { //fail routine
	    
		$vivawallet_data = vRequest::getGet();
		if (!isset($vivawallet_data['s']) || $vivawallet_data['s']=='') {
			return false;
		}	
		$tm_ref = addslashes($vivawallet_data['s']);
		$tm_error = ' Payment failed or cancelled';

		if (!isset($tm_ref)) {
			return;
		}
				
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_payment_plg_vivawallet` WHERE `vivawallet_OrderCode`="' . $tm_ref . '" ';
		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
	    	vmWarn(500, $q . " " . $db->getErrorMsg());
	    	return;
		}
		
		$virtuemart_paymentmethod_id = $paymentTable->virtuemart_paymentmethod_id;
		$order_number = $paymentTable->	order_number;
		$virtuemart_order_id = $paymentTable->virtuemart_order_id;
		$vendorId = 0;
		
		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		
		foreach ($paymentTable as $key => $value) {
			if ($key!='vivawallet_order_state') {
				$dbValues[$key] = $value;
			}
		}

		$dbValues['vivawallet_order_state'] = "F";
		$dbValues['vivawalletresponse_raw'] = "OrderCode: " . $tm_ref;
		$this->storePSPluginInternalData($dbValues, 'virtuemart_order_id', FALSE);

		// Order not found
		if (!$virtuemart_order_id) {
			$html = $this->_getHtmlPaymentResponse('VMPAYMENT_VIVAWALLET_FAILURE_MSG', false);
			vRequest::setVar ('html', $html);
			return null;
		}
		
		if (!class_exists('VirtueMartModelOrders')) {
			require( VMPATH_ADMIN . DS . 'models' . DS . 'orders.php' );
		}
		
		//$order = VirtueMartModelOrders::getOrder($virtuemart_order_id);
		$vmorders= new VirtueMartModelOrders();
		$order=$vmorders->getOrder($virtuemart_order_id);
		$order_status_code = $order['items'][0]->order_status;
		
		if ($order_status_code == 'P') { // not processed
		$html = '<h2>'.$this->_getHtmlPaymentResponse('VMPAYMENT_VIVAWALLET_FAILURE_MSG', false).'</h2>';
		$html .= '<p align="center"><a href="' . JURI::base(true) . '" title="' . vmText::_('VMPAYMENT_VIVAWALLET_HOME') . '">' . vmText::_('VMPAYMENT_VIVAWALLET_HOME') . '&raquo;</a></p>';		
		
		vRequest::setVar ('html', $html);
		$new_status = $method->status_canceled;
		$resp = "FAILED";
		
		$this->managePaymentResponse($virtuemart_order_id, $paymentTable->vivawallet_OrderCode . $tm_result . $tm_error, $resp, $new_status, $paymentTable->vivawallet_custom, $paymentTable->order_number);
		 $this->_clearVivapaySession();
		} //end fail routine
		}//end not processed fail routine


		if(preg_match("/bnkact=success/i", $_SERVER['REQUEST_URI'])) { //success routine
		$vivawallet_data = vRequest::getGet();
		if (!isset($vivawallet_data['s']) || $vivawallet_data['s']=='') {
			return false;
		}		
		
		$tm_ref = addslashes($vivawallet_data['s']);

		if (!isset($tm_ref)) {
			return;
		}
				
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_payment_plg_vivawallet` WHERE `vivawallet_OrderCode`="' . $tm_ref . '" ';
		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
	    	vmWarn(500, $q . " " . $db->getErrorMsg());
	    	return;
		}
		
		$virtuemart_paymentmethod_id = $paymentTable->virtuemart_paymentmethod_id;
		$order_number = $paymentTable->	order_number;
		$virtuemart_order_id = $paymentTable->virtuemart_order_id;
		$vendorId = 0;
		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		
		foreach ($paymentTable as $key => $value) {
			if ($key!='vivawallet_order_state') {
				$dbValues[$key] = $value;
			}
		}
		

		$dbValues['vivawallet_order_state'] = "S";
		$dbValues['vivawalletresponse_raw'] = "OrderCode: " . $tm_ref;
		$this->storePSPluginInternalData($dbValues, 'virtuemart_order_id', FALSE);
		
		// Order not found
		if (!$virtuemart_order_id) {
			$html = $this->_getHtmlPaymentResponse('VMPAYMENT_VIVAWALLET_FAILURE_MSG', false);
			vRequest::setVar ('html', $html);
			return null;
		}
		
		if (!class_exists('VirtueMartModelOrders')) {
			require( VMPATH_ADMIN . DS . 'models' . DS . 'orders.php' );
		}		
		
		//$order = VirtueMartModelOrders::getOrder($virtuemart_order_id);
		$vmorders= new VirtueMartModelOrders();
		$order=$vmorders->getOrder($virtuemart_order_id);
		$order_status_code = $order['items'][0]->order_status;
		
		if ($order_status_code == 'P') { // not processed
		$html = $this->_getHtmlPaymentResponse('VMPAYMENT_VIVAWALLET_SUCCESS_MSG', true,$paymentTable->order_number,$paymentTable->virtuemart_order_id, number_format($paymentTable->payment_order_total, 2, '.', ''), $paymentTable->vivawallet_ref, $paymentTable->vivawallet_instalments, $paymentTable->modified_on, $paymentTable->virtuemart_paymentmethod_id);
		
		vRequest::setVar ('html', $html);
		$new_status = $method->status_success;
		$resp = "SUCCESS";

		$this->managePaymentResponse($virtuemart_order_id, 'TxId: ' . $paymentTable->vivawallet_ref, $resp, $new_status, $paymentTable->vivawallet_custom, $paymentTable->order_number, $paymentTable->vivawallet_instalments);
		 $this->_clearVivapaySession();
		} //end success routine
		}//end not processed success routine


		if(preg_match("/bnkact=webhook/i", $_SERVER['REQUEST_URI'])) { //success routine
		
		$postdata = file_get_contents("php://input");

		$MerchantID =  $method->vivawallet_merchant_id;
		$Password 	=  html_entity_decode($method->vivawallet_merchant_pass);
		
		if($method->vivawallet_production=='1'){
		$curl_adr 	= 'http://demo.vivapayments.com/api/messages/config/token/';
		} else {
		$curl_adr 	= 'https://www.vivapayments.com/api/messages/config/token/';
		}
		
		$curl = curl_init();
		if (preg_match("/https/i", $curl_adr)) {
		curl_setopt($curl, CURLOPT_PORT, 443);
		}
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_URL, $posturl);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
		$curlversion = curl_version();
		if(!preg_match("/NSS/" , $curlversion['ssl_version'])){
		curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, "TLSv1");
		}
		$response = curl_exec($curl);
		
		if(curl_error($curl)){
		if (preg_match("/https/i", $curl_adr)) {
		curl_setopt($curl, CURLOPT_PORT, 443);
		}
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		}
		
		curl_close($curl);
		echo $response;
		
		try {
			
		if(is_object(json_decode($postdata))){
			$resultObj=json_decode($postdata);
		}
		} catch( Exception $e ) {
			echo $e->getMessage();
		}
	
	
		if(sizeof($resultObj->EventData) > 0) {
		$StatusId = $resultObj->EventData->StatusId;
		$OrderCode = $resultObj->EventData->OrderCode;
		$statustr = $this->vivawallet_processing;
		
		$tm_ref = $OrderCode;		
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_payment_plg_vivawallet` WHERE `vivawallet_OrderCode`="' . $tm_ref . '" ';
		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
	    	vmWarn(500, $q . " " . $db->getErrorMsg());
	    	return;
		}
		
		$virtuemart_paymentmethod_id = $paymentTable->virtuemart_paymentmethod_id;
		$order_number = $paymentTable->	order_number;
		$virtuemart_order_id = $paymentTable->virtuemart_order_id;
		$vendorId = 0;
		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		
		foreach ($paymentTable as $key => $value) {
			if ($key!='vivawallet_order_state') {
				$dbValues[$key] = $value;
			}
		}
		

		$dbValues['vivawallet_order_state'] = "S";
		$dbValues['vivawalletresponse_raw'] = "OrderCode: " . $tm_ref;
		$this->storePSPluginInternalData($dbValues, 'virtuemart_order_id', FALSE);
		
		// Order not found
		if (!$virtuemart_order_id) {
			$html = $this->_getHtmlPaymentResponse('VMPAYMENT_VIVAWALLET_FAILURE_MSG', false);
			vRequest::setVar ('html', $html);
			return null;
		}
		
		if (!class_exists('VirtueMartModelOrders')) {
			require( VMPATH_ADMIN . DS . 'models' . DS . 'orders.php' );
		}		
		
		//$order = VirtueMartModelOrders::getOrder($virtuemart_order_id);
		$vmorders= new VirtueMartModelOrders();
		$order=$vmorders->getOrder($virtuemart_order_id);
		$order_status_code = $order['items'][0]->order_status;
		
		if ($order_status_code == 'P' && $StatusId=='F') { // not processed
		$new_status = $method->status_success;
		$resp = "SUCCESS";

		$this->managePaymentResponse($virtuemart_order_id, 'TxId: ' . $paymentTable->vivawallet_ref, $resp, $new_status, $paymentTable->vivawallet_custom, $paymentTable->order_number, $paymentTable->vivawallet_instalments);
		 $this->_clearVivapaySession();
		} //end webhook routine
		}
		}//end not processed webhook routine
				
		return null;
	}

//EB HANDLE RESULT
	function managePaymentResponse($virtuemart_order_id, $ref, $resp, $new_status, $return_context=NULL, $order_number, $period=NULL) {

		if (!class_exists('VirtueMartModelOrders')) {
			require( VMPATH_ADMIN . DS . 'models' . DS . 'orders.php' );
		}
		// save order data
		$modelOrder = new VirtueMartModelOrders();
		$order['order_status'] = $new_status;
		$order['virtuemart_order_id'] = $virtuemart_order_id;
		$order['customer_notified'] = 1;
		
		//VM3 added
		if(isset($period) && $period>1){
		$periodmsg = ' - Instalments: '.$period;
		} else {
		$periodmsg = '';
		}
		
		if ($resp=='SUCCESS'){
		$order['comments'] = vmText::sprintf('VMPAYMENT_VIVAWALLET_PAYMENT_STATUS_CONFIRMED', $order_number) . $ref . $periodmsg;
		} else {
		$order['comments'] = vmText::sprintf('VMPAYMENT_VIVAWALLET_PAYMENT_STATUS_CANCELED', $order_number) . $ref;
		}

		// la fonction updateStatusForOneOrder fait l'envoie de l'email à partir de VM2.0.2
		$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}

		if ($resp=='SUCCESS') {
			$cart = VirtueMartCart::getCart();
			$cart->emptyCart();
			return true;
		}
	}

//EB CANCEL
	function plgVmOnUserPaymentCancel() {

		if (!class_exists('VirtueMartModelOrders'))
		require( VMPATH_ADMIN . DS . 'models' . DS . 'orders.php' );

		$order_number = vRequest::getString('on',0);
		if (!$order_number)
		return false;
		$db = JFactory::getDBO();
		$query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";

		$db->setQuery($query);
		$virtuemart_order_id = $db->loadResult();

		if (!$virtuemart_order_id) {
			return null;
		}
		$this->handlePaymentUserCancel($virtuemart_order_id);

		return true;
	}


//EB PAYMENT NOTIFICATION VALIDATION - CONFIRMATION
	function plgVmOnPaymentNotification() {
		
		if (!class_exists('VirtueMartModelOrders'))
		require( VMPATH_ADMIN . DS . 'models' . DS . 'orders.php' );
		
		$bnkact = vRequest::getString('bnkact', 0);
		
		
		if(isset($bnkact) && $bnkact!=''){
		
		if($bnkact=='validation'){ //validation routine
		$vivawallet_data = vRequest::getPost();
		if (!isset($vivawallet_data['Ref']) || !isset($vivawallet_data['Amount']) || !isset($vivawallet_data['Currency'])) {
			return;
		}

		$tm_ref = addslashes($vivawallet_data['Ref']);
		$tm_amount = addslashes($vivawallet_data['Amount']);
        $tm_currency = addslashes(trim($vivawallet_data['Currency']));
				
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_payment_plg_vivawallet` WHERE `vivawallet_ref`="' . $tm_ref . '" ';
		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
	    	vmWarn(500, $q . " " . $db->getErrorMsg());
	    	return;
		}
		
		$virtuemart_paymentmethod_id = $paymentTable->virtuemart_paymentmethod_id;
		$order_number = $paymentTable->	order_number;
		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		
		 if ((number_format($paymentTable->payment_currency,0) == number_format($tm_currency,0)) && (number_format($paymentTable->payment_order_total, 2, '.', '') == number_format($tm_amount, 2, '.', ''))) {
		 
		foreach ($paymentTable as $key => $value) {
			if ($key!='vivawallet_order_state') {
				$dbValues[$key] = $value;
			}
		}

		$dbValues['vivawallet_order_state'] = "V";
		$this->storePSPluginInternalData($dbValues, 'virtuemart_order_id', true);
		
		 print "[OK]";
		 } else {
		  return;	
		 } 
		exit();
		} //end validation routine
		

		if($bnkact=='confirmation'){ //confirmation routine
		$vivawallet_data = vRequest::getPost();
		if (!isset($vivawallet_data['Ref'])) {
			return;
		}

		$tm_ref = addslashes($vivawallet_data['Ref']);
				
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_payment_plg_vivawallet` WHERE `vivawallet_ref`="' . $tm_ref . '" ';
		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
	    	vmWarn(500, $q . " " . $db->getErrorMsg());
	    	return;
		}
		
		$virtuemart_paymentmethod_id = $paymentTable->virtuemart_paymentmethod_id;
		$order_number = $paymentTable->	order_number;
		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		
		if ($paymentTable->vivawallet_order_state == 'V') {
		 
		foreach ($paymentTable as $key => $value) {
			if ($key!='vivawallet_order_state') {
				$dbValues[$key] = $value;
			}
		}

		$dbValues['vivawallet_order_state'] = "P";
		$this->storePSPluginInternalData($dbValues, 'virtuemart_order_id', true);
		
		 print "[OK]";
		 } else {
		  return;	
		 } 
		 
		exit();
		} //end confirmation routine
		
		
		} else { //$bnkact response is true or not
		return null;
		}
		
	  return null;
	}

//EB STORE RESPONSE DATA
	function _storeVivapayInternalData($method, $vivawallet_data, $virtuemart_order_id) {

		// get all know columns of the table
		$db = JFactory::getDBO();
		$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
		$db->setQuery($query);
		$columns = $db->loadColumn(0);
		$post_msg = '';
		foreach ($vivawallet_data as $key => $value) {
			$post_msg .= $key . "=" . $value . "<br />";
			$table_key = 'vivawallet_response_' . $key;
			if (in_array($table_key, $columns)) {
				$response_fields[$table_key] = $value;
			}
		}

		//$response_fields[$this->_tablepkey] = $this->_getTablepkeyValue($virtuemart_order_id);
		$response_fields['payment_name'] = $this->renderPluginName($method);
		$response_fields['vivawalletresponse_raw'] = $post_msg;
		$return_context = $vivawallet_data['custom'];
		$response_fields['order_number'] = $vivawallet_data['invoice'];
		$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
		//$preload=true   preload the data here too preserve not updated data
		$this->storePSPluginInternalData($response_fields, 'virtuemart_order_id', true);
	}

//EB STORE RESPONSE DATA FUNCTION 
	function _getTablepkeyValue($virtuemart_order_id) {
		$db = JFactory::getDBO();
		$q = 'SELECT ' . $this->_tablepkey . ' FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery($q);

		if (!($pkey = $db->loadResult())) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		return $pkey;
	}

//EB DISPLAY SVAED PAYMENT SETTINGS
    protected function renderPluginName($plugin) {
	$return = '';
	$plugin_name = $this->_psType . '_name';
	$plugin_desc = $this->_psType . '_desc';
	$description = '';
// 		$params = new JParameter($plugin->$plugin_params);
// 		$logo = $params->get($this->_psType . '_logos');
	$logosFieldName = $this->_psType . '_logos';
	$logos = $plugin->$logosFieldName;
	if (!empty($logos)) {
	    $return = $this->displayLogos($logos) . ' ';
	}
	if (!empty($plugin->$plugin_desc)) {
	    $description = '<span class="' . $this->_type . '_description">' . $plugin->$plugin_desc . '</span>';
	}
	$this->_getVivapayIntoSession();
	$extrainfo=$this->getExtraPluginNameInfo($plugin);
	$pluginName = $return . '<span class="' . $this->_type . '_name">' . $plugin->$plugin_name . '</span>' . $description;
	$pluginName.=  $extrainfo ;
	return $pluginName;
    }
	
//EB DISPLAY STORED PAYMENT DATA
	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {

		if (!$this->selectedThisByMethodId($payment_method_id)) {
			return null; // Another method was selected, do nothing
		}

		if (!($paymentTable = $this->_getVivapayInternalData($virtuemart_order_id) )) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		$this->getPaymentCurrency($paymentTable);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $paymentTable->payment_currency . '" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$currency_code_3 = $db->loadResult();
		$html = '<table class="adminlist table">' . "\n";
		$html .=$this->getHtmlHeaderBE();
		$html .= $this->getHtmlRowBE('VIVAWALLET_PAYMENT_NAME', $paymentTable->payment_name);
		//$html .= $this->getHtmlRowBE('PAYPAL_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total.' '.$currency_code_3);
		$code = "vivawallet_response_";
		foreach ($paymentTable as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				$html .= $this->getHtmlRowBE($key, $value);
			}
		}
		$html .= '</table>' . "\n";
		return $html;
	}

//EB GET STORED RESPONSE DATA FUNCTION
	function _getVivapayInternalData($virtuemart_order_id, $order_number='') {
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
		if ($order_number) {
			$q .= " `order_number` = '" . $order_number . "'";
		} else {
			$q .= ' `virtuemart_order_id` = ' . $virtuemart_order_id;
		}

		$db->setQuery($q);
		if (!($paymentTable = $db->loadObject())) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		return $paymentTable;
	}

//EB GET MERCHANT ID
	function _getMerchantId($method) {
		return $method->vivawallet_merchant_id;
	}
	
//EB GET MERCHANT PASS
	function _getMerchantPass($method) {
		return $method->vivawallet_merchant_pass;
	}	
	
//EB GET URL FUNCTION
	function _getVivapayUrl($method) {

		$url = $method->vivawallet_production == '1' ? $method->vivawallet_production_url : $method->vivawallet_test_url;

		return $url;
	}

//EB CHECK PAYMENT CONDITIONS
	protected function checkConditions($cart, $method, $cart_prices) {
		
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount = $cart_prices['salesPrice'];
		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
		OR
		($method->min_amount <= $amount AND ($method->max_amount == 0) ));
		
		//VM3 added
		if(vRequest::getInt('vivinstalments', 0)!=''){
        $this->_vivinstalments = vRequest::getInt('vivinstalments', 0);
        $this->_vivinstalmentsamount = vRequest::getString('vivinstalmentsamount', 0);
		$this->_setVivapayIntoSession();
		}

		$this->_getVivapayIntoSession();
		
		//clear instalments if amount is lowered
		if($this->_vivinstalments>1){
		$instalmentoptions = $this->_getInstalmentOptions($method);
		$split_instal_vivawallet = explode(',', $instalmentoptions);
		$c = count ($split_instal_vivawallet);
		
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_term) = explode(":", $split_instal_vivawallet[$i]);
		
		if($amount < $instal_amount && $instal_term==$this->_vivinstalments){
		$this->_clearVivapaySession();
		}
		}
		}

		$countries = array();
		if (!empty($method->countries)) {
			if (!is_array($method->countries)) {
				$countries[0] = $method->countries;
			} else {
				$countries = $method->countries;
			}
		}
		// probably did not gave his BT:ST address
		if (!is_array($address)) {
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id']))
		$address['virtuemart_country_id'] = 0;
		if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
			if ($amount_cond) {
				
		$lang = JFactory::getLanguage();
		$this->lang_tag = strtolower($lang->getTag());
		if(isset($method->language) && $method->language!=''){
			if (!preg_match("/".$method->language."/i", $this->lang_tag)) {
			return false;
			} 
		}				
				
				return true;
			}
		}
		
		return false;
	}

//EB INSTALL PLUGIN TABLE	 
	function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

		return $this->onStoreInstallPluginTable($jplugin_id);
	}

//EB STORE ADITIONAL PAYMENT DATA	
public function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {

	if (!$this->selectedThisByMethodId(  $cart->virtuemart_paymentmethod_id)) {
	    return null; // Another method was selected, do nothing
	}

	$this->_vivinstalments = vRequest::getInt('vivinstalments', 0);
	$this->_vivinstalmentsamount = vRequest::getString('vivinstalmentsamount', 0);

	$this->_setVivapayIntoSession();
	return true;
    }

//EB DISPLAY PAYMENT OPTIONS ON CHECKOUT 	 
	public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
	JHTML::_('behavior.tooltip');

	if ($this->getPluginMethods($cart->vendorId) === 0) {
	    if (empty($this->_name)) {
		$app = JFactory::getApplication();
		$app->enqueueMessage(vmText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
		return false;
	    } else {
		return false;
	    }
	}
	
		$lang = JFactory::getLanguage();
		$this->lang_tag = $lang->getTag();
		if(strtolower($this->lang_tag) == 'el-gr'){
		$vivawallet_lang = 'GR';
		} else {
		$vivawallet_lang = 'EN';
		}
		
	$html = array();
	$method_name = $this->_psType . '_name';

	JHTML::script('vmcreditcard.js', 'components/com_virtuemart/assets/js/', false);
	JFactory::getLanguage()->load('com_virtuemart');
	vmJsApi::jCreditCard();
	$this->_getVivapayIntoSession(); //get session vars
	$htmla = array(); //7.1 fix
	$html = array();
	foreach ($this->methods as $this->_currentMethod) {
			if ($this->checkConditions($cart, $this->_currentMethod, $cart->cartPrices)) {
				$methodSalesPrice = $this->setCartPrices($cart, $cart->cartPrices, $this->_currentMethod);
				$this->_currentMethod->$method_name = $this->renderPluginName($this->_currentMethod);

		
		$this->getPaymentCurrency($this->_currentMethod);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $this->_currentMethod->payment_currency . '" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$currency_code_3 = $db->loadResult();

		$paymentCurrency = CurrencyDisplay::getInstance($this->_currentMethod->payment_currency);
		$totalInPaymentCurrency = round($paymentCurrency->convertCurrencyTo($this->_currentMethod->payment_currency, $cart->cartPrices['billTotal'], false), 2);
		
		$html = $this->getPluginHtml($this->_currentMethod, $selected, $methodSalesPrice);
		if ($selected == $this->_currentMethod->virtuemart_paymentmethod_id) {
		    if (!empty($vivawalletSession->vivinstalments))
			$this->_vivinstalments = $vivawalletSession->vivinstalments;
			$this->_vivinstalmentsamount = $totalInPaymentCurrency;
			} else {
		    $this->_vivinstalments = '';
			$this->_vivinstalmentsamount = $totalInPaymentCurrency;
		}

		$instalmentoptions = $this->_getInstalmentOptions($this->_currentMethod);

		if(isset($instalmentoptions) && $instalmentoptions!=''){
		
		$html .= '<br /><table border="0" cellspacing="0" cellpadding="2" width="100%">
		    <tr>
		        <td nowrap width="10%" align="right">' . vmText::_('VMPAYMENT_VIVAWALLET_INSTALMENTS_CHECKOUT') . '</td>
		        <td> ';
		$html .= $this->listInstalments('vivinstalments', $this->_vivinstalments, '', $this->_currentMethod, $cart);
		$html .=  '<input type="hidden" name="vivinstalmentsamount" value="'.$this->_vivinstalmentsamount.'"></td></tr></table></span>';
		
		}//end if instalment options are set


		$htmla[] = $html;
	    }
	}
	$htmlIn[] = $htmla;

	return true;

	}

//EB DISPLAY PAYMENT OPTIONS ON CHECKOUT HELPER	
	function _getInstalmentOptions($method) {
	return $method->vivawallet_instalments;
    }	

//EB DISPLAY PAYMENT INSTALMENTS CHARGE ON CHECKOUT HELPER	
	function _getInstalmentCharge($method) {
	return $method->vivawallet_instalments_charge;
    }	
	
//EB DISPLAY PAYMENT OPTIONS ON CHECKOUT HELPER	
	function listInstalments($list_name, $selected=false, $class='', $method, $cart) {
		$this->getPaymentCurrency($method);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$currency_code_3 = $db->loadResult();
		$period_amount ='';

		$paymentCurrency = CurrencyDisplay::getInstance($method->payment_currency);
		$totalInPaymentCurrency = round($paymentCurrency->convertCurrencyTo($method->payment_currency, $cart->cartPrices['billTotal'], false), 2);
		
		$options = array();
		if (!$selected) $selected = 1;

		$instalmentoptions = $this->_getInstalmentOptions($method);
		$split_instal_vivawallet = explode(',', $instalmentoptions);
		$c = count ($split_instal_vivawallet);
		$options[] = JHTML::_('select.option', '1', vmText::_('VMPAYMENT_VIVAWALLET_INSTALMENTS_NO'));
		
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_term) = explode(":", $split_instal_vivawallet[$i]);
		
		if($totalInPaymentCurrency >= $instal_amount){
		
		$period_amount = round(($totalInPaymentCurrency / $instal_term), 2);
		$period_amount = number_format($period_amount, 2, '.', '');
		
		$options[] = JHTML::_('select.option', $instal_term, $instal_term . vmText::_('VMPAYMENT_VIVAWALLET_INSTALMENTS_CHECKOUT_OPTION'));
		}
		}
	
		//VM3 change
		return JHTML::_('select.genericlist', $options, $list_name, 'onchange="this.form.submit()"', 'value', 'text', $selected);

	}	

//EB SESSION PAYMENT OPTIONS ON CHECKOUT
    function _setVivapayIntoSession() {
	$session = JFactory::getSession();
	$sessionVivapay = new stdClass();
	$sessionVivapay->vivinstalments = $this->_vivinstalments;
	$sessionVivapay->vivinstalmentsamount = $this->_vivinstalmentsamount;
	$session->set('vivawallet', json_encode($sessionVivapay), 'vm');
    }

//EB SESSION PAYMENT OPTIONS ON CHECKOUT  
	function _getVivapayIntoSession() {
	$session = JFactory::getSession();
	$vivawalletSession = $session->get('vivawallet', 0, 'vm');
	if (!empty($vivawalletSession)) {
	    $vivawalletData = json_decode($vivawalletSession);
	    $this->_vivinstalments = $vivawalletData->vivinstalments;
		$this->_vivinstalmentsamount = $vivawalletData->vivinstalmentsamount;
	}
    }

//EB SESSION PAYMENT OPTIONS ON CHECKOUT
    function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart) {

	if (!$this->selectedThisByMethodId( $cart->virtuemart_paymentmethod_id)) {
	    return null; // Another method was selected, do nothing
	}
	$this->_getVivapayIntoSession();
    }

//EB DISPLAY SAVED PAYMENT INFO
    function getExtraPluginNameInfo($activeMethod) {
	$creditCardInfos = '';
    $addpricenote = '';
    
    $this->_method = $activeMethod;
    $paymethod = vRequest::getInt('virtuemart_paymentmethod_id', 0);

	//VM3 change
	if(isset($this->addprice)){
	$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $this->addpricecurrency . '" ';
	$db = JFactory::getDBO();
	$db->setQuery($q);
	$currency_code_3 = $db->loadResult();
	
	
	if($this->addprice > 0){
	$addpricenote = " (+ " . number_format($this->addprice, 2, ',', '')." ".$currency_code_3.")";
	} 
	}
	    if($this->_vivinstalments > 1){
		$creditCardInfos .= ' <span class="vmpayment_cardinfo">' . $addpricenote . '</span>';
		}
        
		$paymentModel = VmModel::getModel('Paymentmethod');
		$this->payments = $paymentModel->getPayments(true, false);
		
		if(isset($activeMethod->vivawallet_instalments) && $activeMethod->vivawallet_instalments!=''){
		if(count($this->payments) < 2){
		if(isset($_GET['task'])){
		$creditCardInfos .= '';
		} else {
		$creditCardInfos .= '&nbsp;<a href="' . JURI::base() .'index.php?option=com_virtuemart&view=cart&task=editpayment" "target="_self">'. vmText::_('VMPAYMENT_VIVAWALLET_OPTIONS') . '</a>' . '<input type="hidden" name="vivinstalmentsamount" value="'.$this->_vivinstalmentsamount.'"><input type="hidden" name="vivinstalments" value="'.$this->_vivinstalments.'">';
		}
		}
		}
		
        if($this->_method->virtuemart_paymentmethod_id == $paymethod || (count($this->payments) < 2)){
        	return $creditCardInfos;
        }
    
    }

//EB CLEAR SESSION
    function _clearVivapaySession() {
	$session = JFactory::getSession();
	$session->clear('vivawallet', 'vm');
    }		
		

//CALCULATE PAYMENT OPTION COSTS
	public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		
		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
	}

//CALCULATE PAYMENT OPTION COSTS
	public function getCosts (VirtueMartCart $cart, $method, $cart_prices) {

		$cartTotalAmountOrig=!empty($cart_prices['withTax'])? $cart_prices['withTax']:$cart_prices['salesPrice'];
		
		$this->_vivinstalments = vRequest::getInt('vivinstalments', 0);
		$this->addprice = '';
		$this->addpricecurrency = '';
		
		if(isset($this->_vivinstalments) && $this->_vivinstalments > 1){
		$this->_setVivapayIntoSession();
		}
		
		$this->_getVivapayIntoSession();
		if(isset($this->_vivinstalments) && $this->_vivinstalments > 1){
		
		$instalmentcharge = $this->_getInstalmentCharge($this->_currentMethod);
		$this->_currentMethod->vivawallet_instalments_charge = '';
		
		if(isset($instalmentcharge) && $instalmentcharge!=''){
		
		$split_charge_vivawallet = explode(',', $instalmentcharge);
		$c = count ($split_charge_vivawallet);
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_percentage) = explode(":", $split_charge_vivawallet[$i]);
		
		if($this->_vivinstalments == $instal_amount){
		$this->addprice = $cartTotalAmountOrig * $instal_percentage * 0.01;
		$this->addpricecurrency = (string)$cart->pricesCurrency;
		} 
		}//end for
		
		}//end if charge is set
		}//end if instalments are selected
		
		$instalmentoptions = $this->_getInstalmentOptions($this->_currentMethod);
		$split_instal_vivawallet = explode(',', $instalmentoptions);
		$c = count ($split_instal_vivawallet);
		$term_array = array();
		
		if($c > 1){
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_term) = explode(":", $split_instal_vivawallet[$i]);
		if($cartTotalAmountOrig > $instal_amount){
		$term_array[]=$instal_term;
		}
		}
		}
		
		if(!in_array($this->_vivinstalments ,$term_array)){
		$this->addprice = '';
		$this->addpricecurrency = '';
		$this->_clearVivapaySession();
		}
		
		if(isset($this->_currentMethod->cost_per_transaction) && (float)$this->_currentMethod->cost_per_transaction > 0){
		$this->_currentMethod->cost_per_transaction += $this->addprice;
		} else {
		$this->_currentMethod->cost_per_transaction = 0;
		}
		
		if (is_numeric($this->_currentMethod->cost_per_transaction) && is_numeric($this->addprice)) {
		 $this->_currentMethod->cost_per_transaction += $this->addprice;
		}
		
		if(is_numeric($cart_prices['withTax']) && is_numeric($this->_currentMethod->cost_per_transaction)){
		$cart_prices['withTax'] = $cart_prices['withTax'] + $this->_currentMethod->cost_per_transaction;
		} elseif (is_numeric($this->_currentMethod->cost_per_transaction)) {
		$cart_prices['salesPrice'] = $cart_prices['salesPrice'] + $this->_currentMethod->cost_per_transaction;
		}
		
		if (preg_match ('/%$/', $this->_currentMethod->cost_percent_total)) {
			$this->_currentMethod->cost_percent_total = substr ($this->_currentMethod->cost_percent_total, 0, -1);
		} else {
			$this->_currentMethod->cost_percent_total = $this->_currentMethod->cost_percent_total;
		}
		$cartPrice = !empty($cart_prices['withTax'])? $cart_prices['withTax']:$cart_prices['salesPrice'];
		
		if (is_numeric($this->_currentMethod->cost_per_transaction)) {
		return ($this->_currentMethod->cost_per_transaction + ((float)$cartPrice * (float)$method->cost_percent_total * 0.01));
		}
	}
	


//SELECT PAYMENT OPTION	
	function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array()) {
		return $this->onCheckAutomaticSelected($cart, $cart_prices);
	}

//DISPLAY PAYMENT DETAILS CHECKOUT		
	public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	}

//DISPLAY PAYMENT DETAILS ORDER PRINT	
	function plgVmonShowOrderPrintPayment($order_number, $method_id) {
		return $this->onShowOrderPrint($order_number, $method_id);
	}

//PLUGIN SETUP	
	//VM3 change
	function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}
	
	function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
		return $this->declarePluginParams('payment', $name, $id, $data);
	}

	function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
		return $this->setOnTablePluginParams($name, $id, $table);
	}

}
