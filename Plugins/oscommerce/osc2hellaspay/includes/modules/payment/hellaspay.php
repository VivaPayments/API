<?php
class hellaspay {
  var $code, $title, $description, $enabled, $sort_order, $form_action_url;

  // class constructor
  function hellaspay() {
    $this->code = 'hellaspay';
    $this->title = MODULE_PAYMENT_HELLASPAY_TEXT_TITLE;
    $this->description = '';
	$this->password = MODULE_PAYMENT_HELLASPAY_PASSWORD;
    $this->enabled = (MODULE_PAYMENT_HELLASPAY_STATUS == 'True') ? true : false;
    $this->sort_order = MODULE_PAYMENT_HELLASPAY_SORTORDER; 
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

		$fields[] = array('title' => MODULE_PAYMENT_HELLASPAY_INFO);
		
		if(MODULE_PAYMENT_HELLASPAY_INSTAL!=''){
		$split_instal_hellaspay = explode(',', MODULE_PAYMENT_HELLASPAY_INSTAL);
		$c = count ($split_instal_hellaspay);
		
		$instal_hellaspay[] = array('id' =>'', 'text' =>  MODULE_PAYMENT_HELLASPAY_NOINSTAL);
		
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_term) = explode(":", $split_instal_hellaspay[$i]);
		
		if($order->info['total'] >= $instal_amount){
		$instal_hellaspay[] = array('id' =>$instal_term, 'text' =>  $instal_term . ' ' . MODULE_PAYMENT_HELLASPAY_TEXT);
		}
		}
		
		$hpcntr = count($instal_hellaspay);
		if($hpcntr > 1 ){				
		$fields[] = array('title' => MODULE_PAYMENT_HELLASPAY_INSTALMENTS . ' ' . tep_draw_pull_down_menu('instal_hellaspay', $instal_hellaspay));
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
    global $HTTP_POST_VARS, $instal_hellaspay;

   if($_POST['instal_hellaspay'] > 0){
    $confirmation = array('title' => $this->title . ': ' . MODULE_PAYMENT_HELLASPAY_WARNING,
                            'fields' => array(array('title' => MODULE_PAYMENT_HELLASPAY_INSTALMENTS . ' ' . $_POST['instal_hellaspay'])));
							} else {
	$confirmation = array('title' => $this->title . ': ' . MODULE_PAYMENT_HELLASPAY_WARNING);						
							}
    
    return $confirmation;
  }

  function process_button() {
  global $HTTP_POST_VARS, $customer_id, $languages_id, $order, $currencies, $currency, $instal_hellaspay;
	
	$customer_info = $this->customer();
	$customer_email = $customer_info['email'];
	
    $tm_ref_id = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
	$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	
	$amount_standard = $order->info['total'];
	$hellaspay_amount = round($amount_standard * 100);
	
    $language_query = tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$languages_id . "'");
    $winbnklang = tep_db_fetch_array($language_query);
  
    if(strtoupper($winbnklang['code']) == 'GR' || strtoupper($winbnklang['code']) == 'EL'){
     $languagecode = 'el-GR';
    } else {
     $languagecode = 'en-US';
    }	
	
	if($_POST['instal_hellaspay'] > 0){
	$instal = $_POST['instal_hellaspay']; 
	} else {
	$instal = 1;
	}
	
	$poststring['Amount'] = $hellaspay_amount;
	$poststring['RequestLang'] = $languagecode;
	
	$poststring['Email'] = $customer_email;
	$poststring['MaxInstallments'] = $instal;
	$poststring['MerchantTrns'] = $tm_ref_id;
	$poststring['SourceCode'] = MODULE_PAYMENT_HELLASPAY_SOURCE;
	$poststring['PaymentTimeOut'] = MODULE_PAYMENT_HELLASPAY_TIMEOUT;

	if(MODULE_PAYMENT_HELLASPAY_MODE=='True'){
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
	curl_setopt($curl, CURLOPT_USERPWD, MODULE_PAYMENT_HELLASPAY_MERCHANTID.':'.html_entity_decode(MODULE_PAYMENT_HELLASPAY_PASSWORD));  
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
	curl_setopt($curl, CURLOPT_USERPWD, MODULE_PAYMENT_HELLASPAY_MERCHANTID.':'.MODULE_PAYMENT_HELLASPAY_PASSWORD);
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
	
	tep_db_query("insert into hellaspay_data (OrderCode, ErrorCode, ErrorText, Timestamp, ref, total_cost, currency, order_state, sessionid) values ('".$OrderCode."','".$ErrorCode."','".$ErrorText."',now(),'".$tm_ref_id."','".$hellaspay_amount."','".MODULE_PAYMENT_HELLASPAY_CURRENCY."','I','". tep_session_id() . "')");

	$process_button_string = 
    tep_draw_hidden_field('OrderCode', $OrderCode);
	
    return $process_button_string;
  }

  function before_process() {
   global $HTTP_POST_VARS, $HTTP_GET_VARS;
   
	if(!isset($_GET['act']) || $_GET['act']==''){
	$actionurl = (MODULE_PAYMENT_HELLASPAY_MODE == 'True') ? 'http://demo.vivapayments.com/web/newtransaction.aspx?Ref='.$_POST['OrderCode'] : 'https://www.vivapayments.com/web/newtransaction.aspx?Ref='.$_POST['OrderCode'];
	header("Location: $actionurl");
    exit();
	} else {
	if ($_GET['act'] == 'hellaspay' && $_GET['status'] != 'success') {
		tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_HELLASPAY_TEXT_ERROR_MESSAGE), 'SSL', true, false));
		exit();
		}
	}

  }

  function after_process() {
	global $HTTP_POST_VARS, $HTTP_GET_VARS, $order, $insert_id, $hellaspay_orderID;
	
	   $tm_ref = tep_db_prepare_input($_GET['s']);
	   $sid_query = tep_db_query("select * from hellaspay_data where OrderCode='".$tm_ref."'");
	   $tm_sid = tep_db_fetch_array($sid_query);
	   
	   if($tm_sid['order_state']=='I' && $_GET['act'] == 'hellaspay' && $_GET['status'] == 'success'){
	   
	   tep_db_query("update hellaspay_data set order_state = 'P' where OrderCode='".$tm_ref."'");
	   
	   tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)MODULE_PAYMENT_HELLASPAY_ORDER_STATUS_ID . "', last_modified = now() where orders_id = '" . (int)$insert_id . "'");
	   
	   $sql_data_array = array('orders_id' => (int)$insert_id, 
                                'orders_status_id' => (int)MODULE_PAYMENT_HELLASPAY_ORDER_STATUS_ID, 
                                'date_added' => 'now()', 
                                'customer_notified' => '0',
                                'comments' => 'OrderCode: ' . $tm_ref);

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		} 
  }

  function get_error($error='') {
    global $HTTP_GET_VARS;
   
    if ( $error == "" ) {
       $error = $HTTP_GET_VARS['error'];
    }
 
    $error = array('title' => MODULE_PAYMENT_HELLASPAY_TEXT_ERROR,
		   'error' => stripslashes(urldecode($error)));
    
    return $error;
  }

  function output_error() {
    return false;
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_HELLASPAY_STATUS'");
      $this->_check = tep_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Viva Payments Module', 'MODULE_PAYMENT_HELLASPAY_STATUS', 'True', 'Do you want to accept Viva Payments payments?', '6', '10', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MerchantID', 'MODULE_PAYMENT_HELLASPAY_MERCHANTID', '9999999', 'Merchant ID', '6', '20', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Key', 'MODULE_PAYMENT_HELLASPAY_PASSWORD', '', 'Transaction Password', '6', '30', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Viva Payments SourceCode', 'MODULE_PAYMENT_HELLASPAY_SOURCE', '', 'Source code as specified in Viva Payments Web-care', '6', '45', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Currency', 'MODULE_PAYMENT_HELLASPAY_CURRENCY', '978', 'The currency used for this shop (default 978 for Euro)', '6', '50', now())");	
	
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max allowed Instalments', 'MODULE_PAYMENT_HELLASPAY_INSTAL', '120:3,240:6,360:9,480:12', 'Specify comma separated instalment order total:months like:<br>120:3,240:6,360:9,480:12', '6', '70', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Test Mode', 'MODULE_PAYMENT_HELLASPAY_MODE', 'False', 'Enable the testing mode?', '6', '80', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");	
	
tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transaction Timeout', 'MODULE_PAYMENT_HELLASPAY_TIMEOUT', '300', 'Transaction Timeout in seconds', '6', '85', now())");
	
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Unique sort order', 'MODULE_PAYMENT_HELLASPAY_SORTORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '90', now())");

	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_HELLASPAY_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '100', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
	
tep_db_query("CREATE TABLE IF NOT EXISTS hellaspay_data (
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

    tep_db_query("drop table hellaspay_data");
  }

  function keys() {
    return array(
	'MODULE_PAYMENT_HELLASPAY_STATUS',
	'MODULE_PAYMENT_HELLASPAY_MERCHANTID',
	'MODULE_PAYMENT_HELLASPAY_PASSWORD',
	'MODULE_PAYMENT_HELLASPAY_SOURCE',
	'MODULE_PAYMENT_HELLASPAY_CURRENCY',
	'MODULE_PAYMENT_HELLASPAY_INSTAL',
	'MODULE_PAYMENT_HELLASPAY_MODE',
	'MODULE_PAYMENT_HELLASPAY_TIMEOUT',
	'MODULE_PAYMENT_HELLASPAY_SORTORDER',
	'MODULE_PAYMENT_HELLASPAY_ORDER_STATUS_ID');
  }
}
?>