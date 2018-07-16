<?php
class vivawallet {
  var $code, $title, $description, $enabled, $sort_order, $form_action_url;

  // class constructor
  function vivawallet() {
    $this->code = 'vivawallet';
    $this->title = MODULE_PAYMENT_VIVAWALLET_TEXT_TITLE;
    $this->description = '';
	$this->password = MODULE_PAYMENT_VIVAWALLET_PASSWORD;
    $this->enabled = (MODULE_PAYMENT_VIVAWALLET_STATUS == 'True') ? true : false;
    $this->sort_order = MODULE_PAYMENT_VIVAWALLET_SORTORDER; 
    $this->form_action_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
  }
  
	//get customer info
	function customer() {
	global $order;
	$customer_id = $_SESSION['customer_id'];
	$customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
	$customer = tep_db_fetch_array($customer_query);
	$customer_email =  $customer['customers_email_address'];
	$customer_info = array('fname' => $customer_fname,'lname' => $customer_lname,'email' => $customer_email);
	return $customer_info;
	}

  // class methods
  function javascript_validation() {
    return;
  }

  function selection() {
  global $order, $currencies;

		$fields[] = array('title' => MODULE_PAYMENT_VIVAWALLET_INFO);
		
		if(MODULE_PAYMENT_VIVAWALLET_INSTAL!=''){
		$split_instal_vivawallet = explode(',', MODULE_PAYMENT_VIVAWALLET_INSTAL);
		$c = count ($split_instal_vivawallet);
		
		$instal_vivawallet[] = array('id' =>'', 'text' =>  MODULE_PAYMENT_VIVAWALLET_NOINSTAL);
		
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_term) = explode(":", $split_instal_vivawallet[$i]);
		
		if($order->info['total'] >= $instal_amount){
		$instal_vivawallet[] = array('id' =>$instal_term, 'text' =>  $instal_term . ' ' . MODULE_PAYMENT_VIVAWALLET_TEXT);
		}
		}
		
		$hpcntr = count($instal_vivawallet);
		if($hpcntr > 1 ){				
		$fields[] = array('title' => MODULE_PAYMENT_VIVAWALLET_INSTALMENTS . ' ' . tep_draw_pull_down_menu('instal_vivawallet', $instal_vivawallet));
		}		
		}
		
	  return array('id' => $this->code,
                   'module' => $this->title,
                   'fields' => $fields);				

	}

  function pre_confirmation_check() {
    global $HTTP_POST_VARS, $customer_id, $order;
    return false;
  }

  function confirmation() {
    global $HTTP_POST_VARS, $instal_vivawallet;

   if($_POST['instal_vivawallet'] > 0){
    $confirmation = array('title' => $this->title . ': ' . MODULE_PAYMENT_VIVAWALLET_WARNING,
                            'fields' => array(array('title' => MODULE_PAYMENT_VIVAWALLET_INSTALMENTS . ' ' . $_POST['instal_vivawallet'])));
							} else {
	$confirmation = array('title' => $this->title . ': ' . MODULE_PAYMENT_VIVAWALLET_WARNING);						
							}
    
    return $confirmation;
  }

  function process_button() {
  global $HTTP_POST_VARS, $customer_id, $languages_id, $order, $currencies, $currency, $instal_vivawallet;
	
	$customer_info = $this->customer();
	$customer_email = $customer_info['email'];
	
    $tm_ref_id = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
	$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	
	$amount_standard = $order->info['total'];
	$vivawallet_amount = round($amount_standard * 100);
	
    $language_query = tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$languages_id . "'");
    $winbnklang = tep_db_fetch_array($language_query);
  
    if(strtoupper($winbnklang['code']) == 'GR' || strtoupper($winbnklang['code']) == 'EL'){
     $languagecode = 'el-GR';
    } else {
     $languagecode = 'en-US';
    }	
	
	if($_POST['instal_vivawallet'] > 0){
	$instal = $_POST['instal_vivawallet']; 
	} else {
	$instal = 1;
	}
	
	$poststring['Amount'] = $vivawallet_amount;
	$poststring['RequestLang'] = $languagecode;
	
	$poststring['Email'] = $customer_email;
	$poststring['MaxInstallments'] = $instal;
	$poststring['MerchantTrns'] = $tm_ref_id;
	$poststring['SourceCode'] = MODULE_PAYMENT_VIVAWALLET_SOURCE;
	$poststring['PaymentTimeOut'] = MODULE_PAYMENT_VIVAWALLET_TIMEOUT;

	if(MODULE_PAYMENT_VIVAWALLET_MODE=='True'){
	$curl = curl_init("http://demo.vivapayments.com/api/orders");
	} else {
	$curl = curl_init("https://www.vivapayments.com/api/orders");
	curl_setopt($curl, CURLOPT_PORT, 443);
	}
	
	$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($poststring['MaxInstallments']).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&PaymentTimeOut=300';
	
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, MODULE_PAYMENT_VIVAWALLET_MERCHANTID.':'.html_entity_decode(MODULE_PAYMENT_VIVAWALLET_PASSWORD));  
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
	curl_setopt($curl, CURLOPT_USERPWD, MODULE_PAYMENT_VIVAWALLET_MERCHANTID.':'.html_entity_decode(MODULE_PAYMENT_VIVAWALLET_PASSWORD));
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
	
	tep_db_query("insert into vivawallet_data (OrderCode, ErrorCode, ErrorText, Timestamp, ref, total_cost, currency, order_state, sessionid) values ('".$OrderCode."','".$ErrorCode."','".$ErrorText."',now(),'".$tm_ref_id."','".$vivawallet_amount."','".MODULE_PAYMENT_VIVAWALLET_CURRENCY."','I','". tep_session_id() . "')");

	$process_button_string = 
    tep_draw_hidden_field('OrderCode', $OrderCode);
	
    return $process_button_string;
  }

  function before_process() {
   global $HTTP_POST_VARS, $HTTP_GET_VARS;
   
	if(!isset($_GET['act']) || $_GET['act']==''){
	$actionurl = (MODULE_PAYMENT_VIVAWALLET_MODE == 'True') ? 'http://demo.vivapayments.com/web/newtransaction.aspx?Ref='.$_POST['OrderCode'] : 'https://www.vivapayments.com/web/newtransaction.aspx?Ref='.$_POST['OrderCode'];
	header("Location: $actionurl");
    exit();
	} else {
	if ($_GET['act'] == 'vivawallet' && $_GET['status'] != 'success') {
		tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_VIVAWALLET_TEXT_ERROR_MESSAGE), 'SSL', true, false));
		exit();
		}
	}

  }

  function after_process() {
	global $HTTP_POST_VARS, $HTTP_GET_VARS, $order, $insert_id, $vivawallet_orderID;
	
	   if(isset($_GET['s']) && $_GET['s']!=''){
	   $tm_ref = tep_db_prepare_input($_GET['s']);
	   $sid_query = tep_db_query("select * from vivawallet_data where OrderCode='".$tm_ref."'");
	   $tm_sid = tep_db_fetch_array($sid_query);
	   
	   if($tm_sid['order_state']=='I' && $_GET['act'] == 'vivawallet' && $_GET['status'] == 'success'){
	   
	   tep_db_query("update vivawallet_data set order_state = 'P' where OrderCode='".$tm_ref."'");
	   
	   tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID . "', last_modified = now() where orders_id = '" . (int)$insert_id . "'");
	   
	   $sql_data_array = array('orders_id' => (int)$insert_id, 
                                'orders_status_id' => (int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID, 
                                'date_added' => 'now()', 
                                'customer_notified' => '0',
                                'comments' => 'OrderCode: ' . $tm_ref);

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		} 
	  }
	  
	  if(isset($_GET['status']) && $_GET['status']=='webhook'){
	    $postdata = file_get_contents("php://input");

		$MerchantID = MODULE_PAYMENT_VIVAWALLET_MERCHANTID;
		$Password =  html_entity_decode(MODULE_PAYMENT_VIVAWALLET_PASSWORD);
		
		if(MODULE_PAYMENT_VIVAWALLET_MODE=='True'){
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
		
		$tm_ref = tep_db_prepare_input($OrderCode);
	    $sid_query = tep_db_query("select * from vivawallet_data where OrderCode='".$tm_ref."'");
	    $tm_sid = tep_db_fetch_array($sid_query);
	   
	    if($tm_sid['order_state']=='I' && $StatusId=='F'){
		   tep_db_query("update vivawallet_data set order_state = 'P' where OrderCode='".$tm_ref."'");
		   
		   tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID . "', last_modified = now() where orders_id = '" . (int)$insert_id . "'");
		   
		   $sql_data_array = array('orders_id' => (int)$insert_id, 
									'orders_status_id' => (int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID, 
									'date_added' => 'now()', 
									'customer_notified' => '0',
									'comments' => 'OrderCode: ' . $tm_ref);
	
			tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		}
		}
	  }
  }

  function get_error($error='') {
    global $HTTP_GET_VARS;
   
    if ( $error == "" ) {
       $error = $HTTP_GET_VARS['error'];
    }
 
    $error = array('title' => MODULE_PAYMENT_VIVAWALLET_TEXT_ERROR,
		   'error' => stripslashes(urldecode($error)));
    
    return $error;
  }

  function output_error() {
    return false;
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_VIVAWALLET_STATUS'");
      $this->_check = tep_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Vivawallet Module', 'MODULE_PAYMENT_VIVAWALLET_STATUS', 'True', 'Do you want to accept Vivawallet payments?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MerchantID', 'MODULE_PAYMENT_VIVAWALLET_MERCHANTID', '9999999', 'Merchant ID', '6', '20', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Key', 'MODULE_PAYMENT_VIVAWALLET_PASSWORD', '', 'Transaction Password', '6', '30', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vivawallet SourceCode', 'MODULE_PAYMENT_VIVAWALLET_SOURCE', '', 'Source code as specified in Vivawallet Web-care', '6', '45', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Currency', 'MODULE_PAYMENT_VIVAWALLET_CURRENCY', '978', 'The currency used for this shop (default 978 for Euro)', '6', '50', now())");	
	
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max allowed Instalments', 'MODULE_PAYMENT_VIVAWALLET_INSTAL', '120:3,240:6,360:9,480:12', 'Specify comma separated instalment order total:months like:<br>120:3,240:6,360:9,480:12', '6', '70', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Test Mode', 'MODULE_PAYMENT_VIVAWALLET_MODE', 'False', 'Enable the testing mode?', '6', '80', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");	
	
tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transaction Timeout', 'MODULE_PAYMENT_VIVAWALLET_TIMEOUT', '300', 'Transaction Timeout in seconds', '6', '85', now())");
	
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Unique sort order', 'MODULE_PAYMENT_VIVAWALLET_SORTORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '90', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '100', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
	
tep_db_query("CREATE TABLE IF NOT EXISTS vivawallet_data (
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
  }  

  function remove() {
    tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");

    tep_db_query("drop table vivawallet_data");
  }

  function keys() {
    return array(
	'MODULE_PAYMENT_VIVAWALLET_STATUS',
	'MODULE_PAYMENT_VIVAWALLET_MERCHANTID',
	'MODULE_PAYMENT_VIVAWALLET_PASSWORD',
	'MODULE_PAYMENT_VIVAWALLET_SOURCE',
	'MODULE_PAYMENT_VIVAWALLET_CURRENCY',
	'MODULE_PAYMENT_VIVAWALLET_INSTAL',
	'MODULE_PAYMENT_VIVAWALLET_MODE',
	'MODULE_PAYMENT_VIVAWALLET_TIMEOUT',
	'MODULE_PAYMENT_VIVAWALLET_SORTORDER',
	'MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID');
  }
}
?>