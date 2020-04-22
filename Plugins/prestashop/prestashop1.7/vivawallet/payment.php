<?php
/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/vivawallet.php');

if (!$cookie->isLogged(true))
	Tools::redirect('authentication.php?back=order.php');
elseif (!Customer::getAddressesTotalById((int)($cookie->id_customer)))
	Tools::redirect('address.php?back=order.php?step=1');
	
$vivawallet = new vivawallet();
echo $vivawallet->execPayment($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>
