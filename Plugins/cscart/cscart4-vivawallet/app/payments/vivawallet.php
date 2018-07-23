<?php
/*  Copyright 2017  Vivawallet.com 
 ******************************************************************************
 * @category   Vivawallet Payments Payment Gateway
 * @author     Viva Wallet
 * @copyright  Copyright (c)2017 Vivawallet http://www.vivawallet.com
 ****************************************************************************** 
*/


//
// $Id: vivawallet.php 11696 2013-06-27 09:30:02Z vivawallet $
//
//error_reporting(E_ALL); ini_set("display_errors", 0);

if (!defined('BOOTSTRAP')) { die('Access denied'); }
use Tygh\Registry;

if (defined('PAYMENT_NOTIFICATION')) {
	if ($mode == 'success' && isset($_GET['s']) && $_GET['s']!='') {
	$hpordercode = addslashes($_GET['s']);

	$hpoid = db_get_row("SELECT hp_oid FROM vivawalletdata WHERE hp_code = '".$hpordercode."'");
	$order_id = (strpos($hpoid['hp_oid'], '_')) ? substr($hpoid['hp_oid'], 0, strpos($hpoid['hp_oid'], '_')) : $hpoid['hp_oid'];
		
		$order_info = fn_get_order_info($order_id);
		$processor_data = fn_get_payment_method_data($order_info['payment_id']);

		if ($order_info['payment_info']['OrderCode'] == $_GET['s']) {
			$pp_response['order_status'] = 'P';
			$pp_response['transaction_id'] = $hpordercode;
			$pp_response['reason_text'] = 'Processed';
		} else {
			$pp_response['order_status'] = 'F';
			$pp_response['reason_text'] = __('vivawallet_transaction_fail');
		}

		if (fn_check_payment_script('vivawallet.php', $order_id)) {
			fn_finish_payment($order_id, $pp_response, true);
		}

		fn_order_placement_routines('route', $order_id);
	}


	if ($mode == 'fail' && isset($_GET['s']) && $_GET['s']!='') {
	$hpordercode = addslashes($_GET['s']);

	$hpoid = db_get_row("SELECT hp_oid FROM vivawalletdata WHERE hp_code = '".$hpordercode."'");
	$order_id = (strpos($hpoid['hp_oid'], '_')) ? substr($hpoid['hp_oid'], 0, strpos($hpoid['hp_oid'], '_')) : $hpoid['hp_oid'];

		$order_info = fn_get_order_info($order_id);
		$processor_data = fn_get_payment_method_data($order_info['payment_id']);

		$pp_response['order_status'] = 'F';
		$pp_response["reason_text"] = __('vivawallet_transaction_fail');
		$pp_response['transaction_id'] = $hpordercode;

		if (fn_check_payment_script('vivawallet.php', $order_id)) {
			fn_finish_payment($order_id, $pp_response, false);
			fn_order_placement_routines('route', $order_id, false);
		}
		
	}

} else {

	$cart_order_id = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;

	$payment_info = $order_info['payment_info'];

	if (!empty($payment_info['installments']) && $payment_info['installments'] > 1) {
		$vivawallet_period = $payment_info['installments'];
	} else {
		$vivawallet_period = '1';
	}
	
	$languages = array('GR', 'EL', 'gr', 'el');
	if (in_array(CART_LANGUAGE, $languages)) {
		$formlang = 'el-GR';
	} else {
		$formlang = 'en-US';
	}
	
	$currencies = Registry::get('currencies');
	$currency_code = $processor_data['processor_params']['currency_id'];

	foreach ($currencies as $k => $v) {
		if ($k == $currency_code) {
			$amount = fn_format_price($order_info['total'] / $v['coefficient']);
			$vivawallet_total = number_format((float)$amount, 2, '.', '');	
		}
	}
	
	if(!isset($vivawallet_total) || $vivawallet_total < 1){
	$amount = fn_format_price($order_info['total']);
	$vivawallet_total = number_format((float)$amount, 2, '.', '');
	}
	
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
	
	$mref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
	$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	$amountcents = round($vivawallet_total * 100);
	
	$poststring['MerchantID'] =  $processor_data['processor_params']['merchant_id'];
	$poststring['Password'] =   html_entity_decode($processor_data['processor_params']['password']);
	$poststring['Amount'] = $amountcents;
	$poststring['RequestLang'] = $formlang;
	$poststring['Email'] = $order_info['email'];
	if($vivawallet_period > 1){
	$poststring['MaxInstallments'] = $vivawallet_period;
	} else {
	$poststring['MaxInstallments'] = '1';
	}
	$poststring['MerchantTrns'] = $cart_order_id . "_REF".substr(md5(uniqid(rand(), true)), 0, 9);
	$poststring['SourceCode'] = $processor_data['processor_params']['source'];
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
		fn_update_order_payment_info($order_id, $pp_response);	
		$_SESSION['stored_vivawallet_orderid'] = $order_id;
		
		
echo <<<EOT
<form action="https://www.vivapayments.com/web/newtransaction.aspx" method="GET" name="process"> 
<input type="hidden" name="Ref" value="{$pp_response['OrderCode']}" />
EOT;

$msg = __('text_cc_processor_connection', array(
    '[processor]' => 'Viva Payments server'
));
echo <<<EOT
    </form>
    <p><div align=center>{$msg}</div></p>
    <script type="text/javascript">
    window.onload = function(){
        document.process.submit();
    };
    </script>
 </body>
</html>
EOT;
die();
}
exit;