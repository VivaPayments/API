<?php
  class vivawallet {
    var $code, $title, $description, $enabled;

// class constructor
    function vivawallet() {
      global $order;

      $this->code = 'vivawallet';
      $this->title = MODULE_PAYMENT_VIVAWALLET_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_VIVAWALLET_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_VIVAWALLET_SORT_ORDER;
	  $this->selected_currency = MODULE_PAYMENT_VIVAWALLET_CURRENCY;
      $this->enabled = ((MODULE_PAYMENT_VIVAWALLET_STATUS == 'True') ? true : false);
	  $this->form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');

      if ((int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
     
    }


// class methods
    function update_status() {
      global $order, $db;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_VIVAWALLET_ZONE > 0) ) {
        $check_flag = false;
        $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_VIVAWALLET_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while (!$check->EOF) {
          if ($check->fields['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
          $check->MoveNext();
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }


	function selection() {
		global $order;
		
		
		$ordertotal = round($order->info['total']);
		$split_instal_bank = explode(',', MODULE_PAYMENT_VIVAWALLET_INSTAL);
		$c = count ($split_instal_bank);
		
		$instal_bank[] = array('id' =>'', 'text' => MODULE_PAYMENT_VIVAWALLET_NOINSTAL);
		
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_term) = explode(":", $split_instal_bank[$i]);
		
		if($ordertotal >= $instal_amount){
		$instal_bank[] = array('id' =>$instal_term, 'text' =>  $instal_term . ' ' . MODULE_PAYMENT_VIVAWALLET_INSTALMENTS_TEXT);
		}
		}
		
			
		$selection = array('id' => $this->code,
							'module' => $this->title,
							'fields' => array(array('title' => MODULE_PAYMENT_VIVAWALLET_INSTALMENTS,
									'field' => zen_draw_pull_down_menu('instal_bank', $instal_bank))));
		
		return $selection;
	}

    function pre_confirmation_check() {
      return false;
    }

	function confirmation() {
	 global $HTTP_POST_VARS;
		
		 if($_POST['instal_bank'] > 0){
		 $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_VIVAWALLET_INSTALMENTS . ' ' . $this->_instalments,
													'field' => ''),		
													array('title' => MODULE_PAYMENT_VIVAWALLET_WARNING,
													'field' => '')));
		} else {
		$confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_VIVAWALLET_WARNING,
													'field' => '')));
		
		}											
	
		return $confirmation;
	}

    function process_button() {
      global $db, $HTTP_POST_VARS, $order, $currencies, $currency, $insert_id, $languages_id;
	  
	  $language_query =  $db->Execute("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$languages_id . "'");
	  $winbnklang = $language_query->fields['code'];
	  if(strtoupper($winbnklang) == 'GR' || strtoupper($winbnklang) == 'EL'){
	  $languagecode = 'el-GR';
	  } else {
	  $languagecode = 'en-US';
	  }	
	  
	  if($_POST['instal_bank'] > 0){
	  $instalments = (int)$_POST['instal_bank'] > 0;
	  } else {
	  $instalments = '1';
	  }
	  
	  $set_currency = $this->selected_currency;
	  $amount_standard = number_format($order->info['total'] * $currencies->get_value($set_currency), '2', '.', '');
	  $vivawallet_amount = round($amount_standard * 100);
	  
	  $tm_ref_id = 'REF'.substr(md5(uniqid(rand(), true)), 0, 9);
	  $TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	  
	  $currency_symbol ='';
		switch ($set_currency) {
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
	  
	    $MerchantID = MODULE_PAYMENT_VIVAWALLET_MERCHANTID;
		$Password =  html_entity_decode(MODULE_PAYMENT_VIVAWALLET_PASSWORD);
		
		$poststring['Amount'] = $vivawallet_amount;
		$poststring['RequestLang'] = $languagecode;
		$poststring['MaxInstallments'] = $instalments;
		$poststring['MerchantTrns'] = $tm_ref_id;
		$poststring['SourceCode'] = MODULE_PAYMENT_VIVAWALLET_SOURCE;
		$poststring['CurrencyCode'] = $currency_symbol;
		$poststring['PaymentTimeOut'] = MODULE_PAYMENT_VIVAWALLET_TIMEOUT;
		
	if(MODULE_PAYMENT_VIVAWALLET_MODE=='True'){
	$curl = curl_init("https://demo.vivapayments.com/api/orders");
	curl_setopt($curl, CURLOPT_PORT, 443);
	} else {
	$curl = curl_init("https://www.vivapayments.com/api/orders");
	curl_setopt($curl, CURLOPT_PORT, 443);
	}
	
	$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($poststring['MaxInstallments']).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&CurrencyCode='.urlencode($poststring['CurrencyCode']).'&PaymentTimeOut=300';
	
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
	
	$db->Execute("insert into vivawallet_data (OrderCode, ErrorCode, ErrorText, Timestamp, ref, total_cost, currency, order_state, sessionid) values ('".$OrderCode."','".$ErrorCode."','".$ErrorText."',now(),'".$tm_ref_id."','".$vivawallet_amount."','".$set_currency."','I','". zen_session_id() . "')");
	

      $process_button_string = zen_draw_hidden_field('OrderCode', $OrderCode);

     return $process_button_string;
    }

	function before_process() {
		global $HTTP_POST_VARS, $HTTP_GET_VARS;
		
		if(!isset($_GET['act']) || $_GET['act']==''){
		$actionurl = (MODULE_PAYMENT_VIVAWALLET_MODE == 'True') ? 'https://demo.vivapayments.com/web/newtransaction.aspx?Ref='.$_POST['OrderCode'] : 'https://www.vivapayments.com/web/newtransaction.aspx?Ref='.$_POST['OrderCode'];
		header("Location: $actionurl");
		exit();
		} else {
		if ($_GET['act'] == 'vivawallet' && $_GET['status'] != 'success') {
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_VIVAWALLET_TEXT_ERROR_MESSAGE), 'SSL', true, false));
			exit();
			}
		}
		//return false;
	}	

	function after_process() {	
	
	  global $db, $HTTP_POST_VARS, $HTTP_GET_VARS, $order, $insert_id;
	   $tm_ref = zen_db_prepare_input($_GET['s']);
	   $check = $db->Execute("select * from vivawallet_data where OrderCode='".$tm_ref."'");
	   
	   if($check->fields['order_state']=='I' && $_GET['act'] == 'vivawallet' && $_GET['status'] == 'success'){
	   $db->execute("update vivawallet_data set order_state = 'P' where OrderCode='".$tm_ref."'");
	   $db->execute("update " . TABLE_ORDERS . " set orders_status = '" . (int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID . "', last_modified = now() where orders_id = '" . (int)$insert_id . "'");
	   $sql_data_array = array('orders_id' => (int)$insert_id, 
                                'orders_status_id' => (int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID, 
                                'date_added' => 'now()', 
                                'customer_notified' => '0',
                                'comments' => 'OrderCode: ' . $tm_ref);

        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		} elseif($check->fields['order_state']=='I' && $_GET['act'] == 'vivawallet' && $_GET['status'] == 'webhook'){
		$postdata = file_get_contents("php://input");

		$MerchantID = MODULE_PAYMENT_VIVAWALLET_MERCHANTID;
		$Password =  html_entity_decode(MODULE_PAYMENT_VIVAWALLET_PASSWORD);
		
		if(MODULE_PAYMENT_VIVAWALLET_MODE=='True'){
		$curl_adr 	= 'https://demo.vivapayments.com/api/messages/config/token/';
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
			
	   $check = $db->Execute("select * from vivawallet_data where OrderCode='".$OrderCode."'");
	   if($check->fields['order_state']=='I' && $_GET['act'] == 'vivawallet' && $_GET['status'] == 'webhook' && $StatusId=='F'){
				   
	   $db->execute("update vivawallet_data set order_state = 'P' where OrderCode='".$tm_ref."'");
	   $db->execute("update " . TABLE_ORDERS . " set orders_status = '" . (int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID . "', last_modified = now() where orders_id = '" . (int)$insert_id . "'");
	   $sql_data_array = array('orders_id' => (int)$insert_id, 
                                'orders_status_id' => (int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID, 
                                'date_added' => 'now()', 
                                'customer_notified' => '0',
                                'comments' => 'OrderCode: ' . $OrderCode);

        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		}
	  }
	} 

  }
	
	function get_error() {
	
		global $HTTP_GET_VARS;	
		$error = array('title' => MODULE_PAYMENT_VIVAWALLET_TEXT_ERROR, 
						'error' => stripslashes(urldecode($HTTP_GET_VARS['ErrorMessage'])));
		return $error;
	}


    function check() {
      global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_VIVAWALLET_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }
	

	function install() {
	  global $db, $messageStack;
      if (defined('MODULE_PAYMENT_VIVAWALLET_STATUS')) {
        $messageStack->add_session('Vivawallet module already installed.', 'error');
        zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=vivawallet', 'NONSSL'));
        return 'failed';
      }
	  
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Vivawallet Module', 'MODULE_PAYMENT_VIVAWALLET_STATUS', 'True', 'Do you want to accept Vivawallet?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MerchantID', 'MODULE_PAYMENT_VIVAWALLET_MERCHANTID', '', 'MerchantID used for the Vivawallet', '6', '0', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Key', 'MODULE_PAYMENT_VIVAWALLET_PASSWORD', '', 'Viva API Key', '6', '0', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vivawallet SourceCode', 'MODULE_PAYMENT_VIVAWALLET_SOURCE', '', 'Source code as specified in Vivawallet Web-care', '6', '0', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Instalment Logic', 'MODULE_PAYMENT_VIVAWALLET_INSTAL', '', 'Example: 300:3,600:12<br>Explained: order total 300: allow 3 instalments, order total 600: allow 3 and 6 instalments)', '6', '0', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Test Mode', 'MODULE_PAYMENT_VIVAWALLET_MODE', 'False', 'Enable the testing mode?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transaction Timeout', 'MODULE_PAYMENT_VIVAWALLET_TIMEOUT', '300', 'Transaction Timeout in seconds', '6', '0', now())");

		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_VIVAWALLET_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_VIVAWALLET_CURRENCY', 'Selected Currency', 'Which currency should the order be sent to Viva?', '6', '3', 'zen_cfg_select_option(array(\'Selected Currency\', \'EUR\', \'GBP\', \'BGN\', \'RON\', ', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_VIVAWALLET_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())"); 
	
	$db->Execute("CREATE TABLE IF NOT EXISTS vivawallet_data (
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
      global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	  $db->Execute("DROP TABLE IF EXISTS vivawallet_data");
    }
	
	
	function keys() {
		return array(
		'MODULE_PAYMENT_VIVAWALLET_STATUS', 
		'MODULE_PAYMENT_VIVAWALLET_MERCHANTID', 
		'MODULE_PAYMENT_VIVAWALLET_PASSWORD',
		'MODULE_PAYMENT_VIVAWALLET_SOURCE',
		'MODULE_PAYMENT_VIVAWALLET_INSTAL',
		'MODULE_PAYMENT_VIVAWALLET_MODE',
		'MODULE_PAYMENT_VIVAWALLET_TIMEOUT',
		'MODULE_PAYMENT_VIVAWALLET_CURRENCY',
		'MODULE_PAYMENT_VIVAWALLET_ZONE', 
		'MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID', 
		'MODULE_PAYMENT_VIVAWALLET_SORT_ORDER');	  
	}

}

?>
