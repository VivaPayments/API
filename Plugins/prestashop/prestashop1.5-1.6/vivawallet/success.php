<?php
if(isset($_GET['s']) && $_GET['s']!=''){
include(dirname(__FILE__).'/../../config/config.inc.php');
if(substr(_PS_VERSION_,2,1) < 5){
include(dirname(__FILE__).'/../../header.php');
}
include(dirname(__FILE__).'/vivawallet.php');

$vivawallet = new vivawallet();
$errors = '';

  $OrderCode = addslashes($_GET['s']);
  
  $update_query = "update vivawallet_data set order_state='P' where OrderCode='".$OrderCode."'";
  $update = Db::getInstance()->execute($update_query);

  $transtat_query = "select * from vivawallet_data where OrderCode='".$OrderCode."' ORDER BY id DESC";
  $transtat = Db::getInstance()->executeS($transtat_query, $array = true, $use_cache = 0);

  $currency_payment = Db::getInstance()->getValue('SELECT id_currency FROM '._DB_PREFIX_.'currency WHERE iso_code = "'.$transtat[0]['currency'].'"');
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
   
//tommodps15 - redirect to controller
   if(substr(_PS_VERSION_,2,1) > 4){
   Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cartid.'&id_module='.$vivawallet->id.'&id_order='.$vivawallet->currentOrder.'&key='.$secure_key);
   } else {
   Tools::redirectLink(__PS_BASE_URI__."order-confirmation.php?id_cart={$cartid}&id_module={$vivawallet->id}&id_order={$vivawallet->currentOrder}&key={$secure_key}");
   }

} else {
echo 'No valid input received.';
}
?>