<?php
/*  Copyright 2020  Vivawallet.com 
 ******************************************************************************
 * @category   Vivawallet Payments Payment Gateway
 * @author     Viva Wallet
 * @copyright  Copyright (c)2020 Vivawallet http://www.vivawallet.com
 ****************************************************************************** 
*/


//
// $Id: vivawallet.php 11696 2013-01-11 09:30:02Z vivawallet $
//
error_reporting(E_ALL); ini_set("display_errors", 0);

if ( !defined('AREA') ) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

	if ($mode == 'success' && isset($_GET['s']) && $_GET['s']!='') {
	$hpordercode = addslashes($_GET['s']);

	$hpoid = db_get_row("SELECT hp_oid FROM vivawalletdata WHERE hp_code = '".$hpordercode."'");
	$order_id = (strpos($hpoid['hp_oid'], '_')) ? substr($hpoid['hp_oid'], 0, strpos($hpoid['hp_oid'], '_')) : $hpoid['hp_oid'];
		
		$order_info = fn_get_order_info($order_id);
		$processor_data = fn_get_payment_method_data($order_info['payment_id']);

		if ($order_info['payment_info']['OrderCode'] == $_GET['s']) {
			$pp_response['order_status'] = 'P';
			$pp_response['reason_text'] = 'Processed';
		} else {
			$pp_response['order_status'] = 'F';
			$pp_response['reason_text'] = fn_get_lang_var('vivawallet_transaction_fail');
		}

		if (fn_check_payment_script('vivawallet.php', $order_id)) {
			fn_finish_payment($order_id, $pp_response, false);
		}

		//samesite cookie fix
		if(!isset($_SESSION['auth']['user_id']) || $_SESSION['auth']['user_id']==''){
		 $user_id = $order_info['payment_info']['uid'];
		 fn_login_user($user_id, true);
		}
				
		fn_order_placement_routines($order_id);
	}
	
	if ($mode == 'webhook') {
	$payment_id = db_get_field("SELECT ?:payments.payment_id FROM ?:payments LEFT JOIN ?:payment_processors ON ?:payment_processors.processor_id = ?:payments.processor_id WHERE ?:payment_processors.processor_script = 'vivawallet.php'");
    $processor_data = fn_get_payment_method_data($payment_id);
	
	$postdata = file_get_contents("php://input");

	$MerchantID =  $processor_data['params']['merchant_id'];
	$Password =   html_entity_decode($processor_data['params']['password']);
	$curl_adr 	= 'https://www.vivapayments.com/api/messages/config/token/';

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

	$hpoid = db_get_row("SELECT hp_oid FROM vivawalletdata WHERE hp_code = '".$OrderCode."'");
	$order_id = (strpos($hpoid['hp_oid'], '_')) ? substr($hpoid['hp_oid'], 0, strpos($hpoid['hp_oid'], '_')) : $hpoid['hp_oid'];
		
		$order_info = fn_get_order_info($order_id);
		$processor_data = fn_get_payment_method_data($order_info['payment_id']);

		if ($order_info['payment_info']['OrderCode'] == $OrderCode && $StatusId=='F') {
			$pp_response['order_status'] = 'P';
			$pp_response['reason_text'] = 'Processed';
		} else {
			$pp_response['order_status'] = 'F';
			$pp_response['reason_text'] = fn_get_lang_var('vivawallet_transaction_fail');
		}

		if (fn_check_payment_script('vivawallet.php', $order_id)) {
			fn_finish_payment($order_id, $pp_response, false);
		}

		fn_order_placement_routines($order_id);
	}
	}

	if ($mode == 'fail' && isset($_GET['s']) && $_GET['s']!='') {
	$hpordercode = addslashes($_GET['s']);

	$hpoid = db_get_row("SELECT hp_oid FROM vivawalletdata WHERE hp_code = '".$hpordercode."'");
	$order_id = (strpos($hpoid['hp_oid'], '_')) ? substr($hpoid['hp_oid'], 0, strpos($hpoid['hp_oid'], '_')) : $hpoid['hp_oid'];
		
		$order_info = fn_get_order_info($order_id);
		$processor_data = fn_get_payment_method_data($order_info['payment_id']);

		$pp_response['order_status'] = 'N';
		$pp_response["reason_text"] = fn_get_lang_var('vivawallet_transaction_fail');

		if (fn_check_payment_script('vivawallet.php', $order_id)) {
			fn_finish_payment($order_id, $pp_response, false);
		}
		
		//samesite cookie fix
		if(!isset($_SESSION['auth']['user_id']) || $_SESSION['auth']['user_id']==''){
		 $user_id = $order_info['payment_info']['uid'];
		 fn_login_user($user_id, true);
		}

		fn_order_placement_routines($order_id);
	}

} else {

	$cart_order_id = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;

	$payment_info = $order_info['payment_info'];

	if (!empty($payment_info['installments']) && $payment_info['installments'] > 1) {
		$vivawallet_period = $payment_info['installments'];
	} else {
		$vivawallet_period = '1';
	}
	
	$languages = array('GR', 'EL');
	if (in_array(CART_LANGUAGE, $languages)) {
		$formlang = 'el-GR';
	} else {
		$formlang = 'en-US';
	}
	

	$currencies = Registry::get('currencies');
	$currency_code = $processor_data['params']['currency_id'];

	foreach ($currencies as $k => $v) {
		if ($k == $currency_code) {
			$amount = fn_format_price($order_info['total'] / $v['coefficient']);
			$vivawallet_total = number_format($amount, 2, '.', '');	
		}
	}
	
	$mref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
	$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	$amountcents = round($vivawallet_total * 100);
	
	$currency_symbol ='';
		switch ($currency_code) {
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
	
	$poststring['MerchantID'] =  $processor_data['params']['merchant_id'];
	$poststring['Password'] =   html_entity_decode($processor_data['params']['password']);
	$poststring['Amount'] = $amountcents;
	$poststring['RequestLang'] = $formlang;
	$poststring['Email'] = $order_info['email'];
	if($vivawallet_period > 1){
	$poststring['MaxInstallments'] = $vivawallet_period;
	} else {
	$poststring['MaxInstallments'] = '1';
	}
	$poststring['MerchantTrns'] = $cart_order_id . "_REF".substr(md5(uniqid(rand(), true)), 0, 9);
	$poststring['SourceCode'] = $processor_data['params']['source'];
	$poststring['CurrencyCode'] = $currency_symbol;
	$poststring['PaymentTimeOut'] = '300';
	
	$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($poststring['MaxInstallments']).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&CurrencyCode='.urlencode($poststring['CurrencyCode']).'&PaymentTimeOut=300';
	
	$pp_response = array();

	$curl = curl_init("https://www.vivapayments.com/api/orders");
	curl_setopt($curl, CURLOPT_PORT, 443);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, $poststring['MerchantID'].':'.$poststring['Password']);
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
	curl_setopt($curl, CURLOPT_USERPWD, $poststring['MerchantID'].':'.$poststring['Password']);
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
		$pp_response['OrderCode'] = $resultObj->OrderCode;
		$pp_response['ErrorCode'] = $resultObj->ErrorCode;
		$pp_response['ErrorText'] = $resultObj->ErrorText;
		}
		else{
			throw new Exception("Unable to create order code (" . $resultObj->ErrorText . ")");
		}
	
		db_query("INSERT INTO vivawalletdata (hp_oid, hp_code) VALUES ('".$cart_order_id."', '".$pp_response['OrderCode']."')");
		
		//samesite cookie fix
		$pp_response['uid'] = $_SESSION['auth']['user_id'];
		
		fn_update_order_payment_info($order_id, $pp_response);	
		$_SESSION['stored_vivawallet_orderid'] = $order_id;
	
		
echo <<<EOT
<html>
<body onLoad="javascript: document.process.submit();">
<form action="https://www.vivapayments.com/web/newtransaction.aspx" method="GET" name="process"> 
<input type="hidden" name="Ref" value="{$pp_response['OrderCode']}" />
EOT;
$msg = fn_get_lang_var('text_cc_processor_connection');
$msg = str_replace('[processor]', 'VivaWallet Payments server', $msg);
echo <<<EOT
	</form>
	<p><div align=center>{$msg}</div></p>
 </body>
</html>
EOT;
die();
}
exit;
?>
