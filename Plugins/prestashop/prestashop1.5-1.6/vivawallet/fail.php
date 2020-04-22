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
  
  $update_query = "update vivawallet_data set order_state='F' where OrderCode='".$OrderCode."'";
  $update = Db::getInstance()->execute($update_query);

  $transtat_query = "select * from vivawallet_data where OrderCode='".$OrderCode."' ORDER BY id DESC";
  $transtat = Db::getInstance()->executeS($transtat_query, $array = true, $use_cache = 0);

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
 	}

  $errors = 'Transaction failed or cancelled';

   $vivawallet->validateOrder((int)$cartid, _PS_OS_ERROR_, 0, $vivawallet->displayName, $errors.'<br />', array(), NULL, false, $secure_key, $shop);  

   if(substr(_PS_VERSION_,2,1) > 4){
   Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cartid.'&id_module='.$vivawallet->id.'&id_order='.$vivawallet->currentOrder.'&key='.$secure_key);
   } else {
   Tools::redirectLink(__PS_BASE_URI__."order-confirmation.php?id_cart={$cartid}&id_module={$vivawallet->id}&id_order={$vivawallet->currentOrder}&key={$secure_key}");
   }
}  else {
echo 'No valid input received.';
}     
?>