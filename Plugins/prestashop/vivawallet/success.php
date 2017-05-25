<?php
if(isset($_GET['s']) && $_GET['s']!=''){
include(dirname(__FILE__).'/../../config/config.inc.php');
if(substr(_PS_VERSION_,2,1) < 5){
include(dirname(__FILE__).'/../../header.php');
}
include(dirname(__FILE__).'/vivawallet.php');

$vivawallet = new vivawallet();
$errors = '';

  $OrderCode = stripslashes($_GET['s']);
  
  $update_query = "update vivawallet_data set order_state='P' where OrderCode='".$OrderCode."'";
  $update = Db::getInstance()->execute($update_query);

  $transtat_query = "select * from vivawallet_data where OrderCode='".$OrderCode."' ORDER BY id DESC";
  $transtat = Db::getInstance()->executeS($transtat_query, $array = true, $use_cache = 0);

  $currency_payment = Db::getInstance()->getValue('SELECT id_currency FROM '._DB_PREFIX_.'currency WHERE iso_code = "'.$emp_currency.'"');
  $total = floatval(number_format(($transtat[0]['total_cost'] / 100), 2, '.', ''));
  $secure_key = $transtat[0]['secure_key'];
  $cartid = $transtat[0]['ref'];
  
  if(substr(_PS_VERSION_,2,1) >= 5){
	Context::getContext()->cart = new Cart((int)$cart_id);
	Context::getContext()->customer = new Customer((int)Context::getContext()->cart->id_customer);
	$address = new Address((int)Context::getContext()->cart->id_address_invoice);
	Context::getContext()->country = new Country((int)$address->id_country);
	$secure_key = $customer->secure_key;
	}
  
  $details = array(
				'id_transaction' => $OrderCode,
				'transaction_id' => $OrderCode
			);

   $vivawallet->validateOrder((int)$cartid, _PS_OS_PAYMENT_, $total, $vivawallet->displayName, 'OrderCode: '.$OrderCode,$details,(int)$currency_payment,false,$secure_key);
   $currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
   $order = new Order($vivawallet->currentOrder);
   
   $delete_query = "delete from vivawallet_data where ref='".$cartid."'";
   $delete = Db::getInstance()->execute($delete_query);
   
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