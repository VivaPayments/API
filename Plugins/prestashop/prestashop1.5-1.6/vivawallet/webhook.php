<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
if(substr(_PS_VERSION_,2,1) < 5){
include(dirname(__FILE__).'/../../header.php');
}
include(dirname(__FILE__).'/vivawallet.php');

$vivawallet = new vivawallet();
$errors = '';

	$postdata = file_get_contents("php://input");

	$MerchantID =  Configuration::get('VIVAWALLET_MERCHANTID');
	$Password =   html_entity_decode(Configuration::get('VIVAWALLET_MERCHANTPASS'));
	$BaseUrl =  trim(Configuration::get('VIVAWALLET_URL'));
	$curl_adr 	= $BaseUrl.'/api/messages/config/token/';

	$curl = curl_init();
	if (preg_match("/https/i", $curl_adr)) {
	curl_setopt($curl, CURLOPT_PORT, 443);
	}
	curl_setopt($curl, CURLOPT_POST, false);
	curl_setopt($curl, CURLOPT_URL, $curl_adr);
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
	
	if(isset($resultObj->EventData->StatusId) && $resultObj->EventData->StatusId=='F') {
	$StatusId = $resultObj->EventData->StatusId;
	$OrderCode = $resultObj->EventData->OrderCode;
	$TransactionId = $resultObj->EventData->TransactionId;

  $transtat_query = "select * from vivawallet_data where OrderCode='".addslashes($OrderCode)."' ORDER BY id DESC";
  $transtat = Db::getInstance()->executeS($transtat_query, $array = true, $use_cache = 0);

  
  if($transtat[0]['order_state']=='I' && $StatusId=='F'){
  $update_query = "update vivawallet_data set order_state='P' where OrderCode='".addslashes($OrderCode)."'";
  $update = Db::getInstance()->execute($update_query);

  $currency_payment = Db::getInstance()->getValue('SELECT id_currency FROM '._DB_PREFIX_.'currency WHERE iso_code = "'.$emp_currency.'"');
  $total = floatval(number_format(($transtat[0]['total_cost'] / 100), 2, '.', ''));
  $secure_key = $transtat[0]['secure_key'];
  $cartid = $transtat[0]['ref'];
  
   if(substr(_PS_VERSION_,2,1) >= 5){
   Context::getContext()->cart = new Cart((int)$cart_id);
   Context::getContext()->cookie->id_cart = (int)$cart_id;
   
   $cart = new Cart((int)$cart_id);
   
	 if (Validate::isLoadedObject($cart)) {
		$shop = new Shop((int)$cart->id_shop);
		if (Validate::isLoadedObject($shop)) {
		 Context::getContext()->shop = $shop;
		}
		
		$customer = new Customer((int)$cart->id_customer);
		if (Validate::isLoadedObject($customer)) {
			$customer->logged = 1;
			Context::getContext()->customer = $customer;
			Context::getContext()->cookie->id_customer = (int)$customer->id;
			Context::getContext()->cookie->customer_lastname = $customer->lastname;
			Context::getContext()->cookie->customer_firstname = $customer->firstname;
			Context::getContext()->cookie->logged = 1;
			Context::getContext()->cookie->check_cgv = 1;
			Context::getContext()->cookie->is_guest = $customer->isGuest();
			Context::getContext()->cookie->passwd = $customer->passwd;
			Context::getContext()->cookie->email = $customer->email;
			$secure_key = $customer->secure_key;
		}
	  }
	  if ((int)Context::getContext()->cookie->id_cart > 0) {
				Context::getContext()->cookie->__unset('id_cart');
	  }
 	}	
	
  
  $details = array(
				'id_transaction' => $OrderCode,
				'transaction_id' => $OrderCode
			);

   $vivawallet->validateOrder((int)$cartid, _PS_OS_PAYMENT_, $total, $vivawallet->displayName, 'OrderCode: '.$OrderCode,$details,(int)$currency_payment,false,$secure_key, $shop);
   $currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
   $order = new Order($vivawallet->currentOrder);

} 
}
?>