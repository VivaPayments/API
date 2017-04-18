<?php
if(isset($_GET['s']) && $_GET['s']!=''){
include(dirname(__FILE__).'/../../config/config.inc.php');
if(substr(_PS_VERSION_,2,1) < 5){
include(dirname(__FILE__).'/../../header.php');
}
include(dirname(__FILE__).'/hellaspay.php');

$hellaspay = new hellaspay();
$errors = '';

  $OrderCode = stripslashes($_GET['s']);
  
  $update_query = "update hellaspay_data set order_state='F' where OrderCode='".$OrderCode."'";
  $update = Db::getInstance()->execute($update_query);

  $transtat_query = "select * from hellaspay_data where OrderCode='".$OrderCode."' ORDER BY id DESC";
  $transtat = Db::getInstance()->executeS($transtat_query, $array = true, $use_cache = 0);

  $secure_key = $transtat[0]['secure_key'];
  $cartid = $transtat[0]['ref'];
  
	if(substr(_PS_VERSION_,2,1) >= 5){
	Context::getContext()->cart = new Cart((int)$cart_id);
	Context::getContext()->customer = new Customer((int)Context::getContext()->cart->id_customer);
	$address = new Address((int)Context::getContext()->cart->id_address_invoice);
	Context::getContext()->country = new Country((int)$address->id_country);
	$secure_key = $customer->secure_key;
	}

  $errors = 'Transaction failed or cancelled';

   $hellaspay->validateOrder((int)$cartid, _PS_OS_ERROR_, 0, $hellaspay->displayName, $errors.'<br />', array(), NULL, false, $secure_key);  

   if(substr(_PS_VERSION_,2,1) > 4){
   Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cartid.'&id_module='.$hellaspay->id.'&id_order='.$hellaspay->currentOrder.'&key='.$secure_key);
   } else {
   Tools::redirectLink(__PS_BASE_URI__."order-confirmation.php?id_cart={$cartid}&id_module={$hellaspay->id}&id_order={$hellaspay->currentOrder}&key={$secure_key}");
   }
}  else {
echo 'No valid input received.';
}     
?>