<?php
  class osC_Payment_vivawallet extends osC_Payment {
    var $_title,
        $_code = 'vivawallet',
        $_status = false,
        $_sort_order,
        $_order_id;

    function osC_Payment_vivawallet() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_vivawallet_title');
      $this->_method_title = $osC_Language->get('payment_vivawallet_method_title');
	  $this->_lang_code = $osC_Language->get('payment_vivawallet_language_code');
      $this->_status = (MODULE_PAYMENT_VIVAWALLET_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_VIVAWALLET_SORT_ORDER;
		
		switch (MODULE_PAYMENT_VIVAWALLET_MODE) {
        case 'Live':
          $this->form_action_url = 'https://www.vivapayments.com/web/newtransaction.aspx" method="get';
          break;
        case 'Testing':
          $this->form_action_url = 'http://demo.vivapayments.com/web/newtransaction.aspx" method="get';
          break;
      }

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_VIVAWALLET_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_VIVAWALLET_ZONE);
          $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
          $Qcheck->execute();

          while ($Qcheck->next()) {
            if ($Qcheck->valueInt('zone_id') < 1) {
              $check_flag = true;
              break;
            } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
              $check_flag = true;
              break;
            }
          }

          if ($check_flag === false) {
            $this->_status = false;
          }
        }
      }
    }

    function selection() {
	global $osC_Currencies, $osC_ShoppingCart, $osC_Language, $osC_Session;
		  
	  return array('id' => $this->_code,
                   'module' => $this->_method_title);
    }

    function confirmation() {
      $this->_order_id = osC_Order::insert();
    }

    function process_button() {
      global $osC_Customer, $osC_Database, $osC_Currencies, $osC_ShoppingCart, $osC_Language, $osC_Session;

      if (MODULE_PAYMENT_VIVAWALLET_CURRENCY == 'Selected Currency') {
        $currency = $osC_Currencies->getCode();
      } else {
        $currency = MODULE_PAYMENT_VIVAWALLET_CURRENCY;
      }
	  
	  $vivawallet_amount = round($osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $currency), 2) * 100;
	  
      $order = $this->_order_id;
	  $ref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
	  $TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	  
	  if(isset($this->_instalments) && (int)$this->_instalments > 0){
	  $instalments = (int)$this->_instalments;
	  } else {
	  $instalments = '0';
	  }
	  
	  if(strtoupper($this->_lang_code) == 'GR' || strtoupper($this->_lang_code) == 'EL'){
  		$languagecode = 'el-GR';
  		} else {
  		$languagecode = 'en-US';
  		} 
	  

	$MerchantID = MODULE_PAYMENT_VIVAWALLET_MERCHANTID;
	$Password =  MODULE_PAYMENT_VIVAWALLET_MERCHANTPASS;
	
	$poststring['Amount'] = $vivawallet_amount;
	$poststring['RequestLang'] = $languagecode;
	$poststring['Email'] = $osC_Customer->getEmailAddress();
	if (MODULE_PAYMENT_VIVAWALLET_INSTAL == 'Deny') {
	$poststring['MaxInstallments'] = '1';
	} else {
	$poststring['MaxInstallments'] = '';
	}
	$poststring['MerchantTrns'] = $order;
	$poststring['SourceCode'] = MODULE_PAYMENT_VIVAWALLET_SOURCE;
	$poststring['PaymentTimeOut'] = '300';

	if(MODULE_PAYMENT_VIVAWALLET_MODE=='Testing'){
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
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.html_entity_decode($Password)); 
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
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.html_entity_decode($Password));
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

	  $Stransaction = $osC_Database->query('insert into :vivawallet_data (OrderCode, ErrorCode, ErrorText, Timestamp, ref, total_cost, currency, order_state, sessionid) values (:OrderCode, :ErrorCode, :ErrorText, now(), :ref, :total, :currency, :state, :sessid)');
            
			$Stransaction->bindTable(':vivawallet_data', 'vivawallet_data');
			$Stransaction->bindValue(':OrderCode', $OrderCode);
			$Stransaction->bindValue(':ErrorCode', $ErrorCode);
            $Stransaction->bindValue(':ErrorText', $ErrorText);
			$Stransaction->bindValue(':ref', $order);
			$Stransaction->bindValue(':total', $vivawallet_amount);
            $Stransaction->bindValue(':currency', '978');
			$Stransaction->bindValue(':state', 'I');
			$Stransaction->bindValue(':sessid', $osC_Session->getID());
            $Stransaction->execute();
      
	  $process_button_string = osc_draw_hidden_field('Ref', $OrderCode);

      return $process_button_string;
    }

    function process() {
    }

    function callback() {
      global $osC_Language, $messageStack, $osC_Database, $osC_Currencies, $osC_ShoppingCart;
	  
	  //fail
	  if(preg_match("/fail/i", $_SERVER['REQUEST_URI'])) {
		
		if(isset($_GET['s']) && $_GET['s'] !='') {

		$getorderinfo = explode(":",$_POST['Param1']);
		$orderid = $getorderinfo[0];
		$ref = $getorderinfo[1];
		
 	  $check_query = "select ref, sessionid from vivawallet_data where OrderCode='".addslashes($_GET['s'])."'";
		$oQuery = $osC_Database->query($check_query);
		$oRecordset = $oQuery->execute();
		
		if(mysql_num_rows($oRecordset)){
	    $oRecord = mysql_fetch_assoc($oRecordset);

		$update_query = "update vivawallet_data set order_state='F' where OrderCode='".addslashes($_GET['s'])."'";
		$uQuery = $osC_Database->query($update_query);
		$uRecordset = $uQuery->execute();

		$error = $osC_Language->get('payment_vivawallet_error_message');
		$messageStack->add_session('checkout', $error, 'error');
		
		osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=paymentInformationForm&sid='.$oRecord['sessionid'], 'SSL', null, null, true));
		}
	  
	  }
	  }//end fail

	  //success
	  if(preg_match("/success/i", $_SERVER['REQUEST_URI'])) {
		
		if(isset($_GET['s']) && $_GET['s'] !='') {

 	  $check_query = "select ref, sessionid from vivawallet_data where OrderCode='".addslashes($_GET['s'])."'";
		$oQuery = $osC_Database->query($check_query);
		$oRecordset = $oQuery->execute();
		
		if(mysql_num_rows($oRecordset)){
	    $oRecord = mysql_fetch_assoc($oRecordset);

		$update_query = "update vivawallet_data set order_state='P' where OrderCode='".addslashes($_GET['s'])."'";
		$uQuery = $osC_Database->query($update_query);
		$uRecordset = $uQuery->execute();

		$comment = 'OrderCode ' . addslashes($_GET['s']);
		 osC_Order::process($oRecord['ref'], $this->order_status, $comment);
		 $osC_ShoppingCart->reset(true);
		 unset($_SESSION['comments']);
		 osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'success&sid='.$oRecord['sessionid'], 'SSL'));

		}
	  
	  }
	  }//end success
	  
	  //webhook
	  if(preg_match("/webhook/i", $_SERVER['REQUEST_URI'])) {
		$postdata = file_get_contents("php://input");

		$MerchantID = MODULE_PAYMENT_VIVAWALLET_MERCHANTID;
		$Password =  html_entity_decode(MODULE_PAYMENT_VIVAWALLET_MERCHANTPASS);
		
		if(MODULE_PAYMENT_VIVAWALLET_MODE=='Testing'){
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

 	    $check_query = "select ref, sessionid from vivawallet_data where order_state='I' and OrderCode='".addslashes($OrderCode)."'";
		$oQuery = $osC_Database->query($check_query);
		$oRecordset = $oQuery->execute();
		
		if(mysql_num_rows($oRecordset) && $StatusId=='F'){
	    $oRecord = mysql_fetch_assoc($oRecordset);

		$update_query = "update vivawallet_data set order_state='P' where OrderCode='".addslashes($OrderCode)."'";
		$uQuery = $osC_Database->query($update_query);
		$uRecordset = $uQuery->execute();

		 $comment = 'OrderCode ' . addslashes($OrderCode);
		 osC_Order::process($oRecord['ref'], $this->order_status, $comment);
		 $osC_ShoppingCart->reset(true);
		}
	   }
	  }//end webhook	  
	  	  	  
  }
}  
?>