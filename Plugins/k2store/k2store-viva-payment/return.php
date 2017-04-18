<?php
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', '../../../');
	require_once JPATH_BASE.'/includes/defines.php';
}

require_once JPATH_BASE.'/includes/framework.php';

if ( $_SERVER['SERVER_PORT'] == "443" ) {
	$Protocol = "https://";
} else {
	$Protocol = "http://";
}

$PATH = preg_replace('@plugins\/k2store\/payment_viva\/return.php@','', $_SERVER['SCRIPT_NAME']);
$URL = $_SERVER['HTTP_HOST'] . $PATH ; 
$redirecturl = $Protocol.$URL.'index.php?option=com_k2store&view=checkout&task=confirmPayment&orderpayment_type=payment_viva&paction=cancel_payment';
 
isset($_GET['s']) ? $ordercode = addslashes($_GET['s']) : $ordercode = '';

if(isset($ordercode) && $ordercode!=''){

$url_ref='&tmref='.$ordercode;
$trid = addslashes($_GET['t']);

$db = JFactory::getDBO();
$q = 'SELECT params FROM `#__extensions` WHERE `name`="plg_k2store_payments_viva" ';
$db->setQuery($q);
if (!($pluginTable = $db->loadObject())) {
	header('Location: ' . $redirecturl);
	exit;
}
$pluginvalue = json_decode($pluginTable->params);

$query = "UPDATE #__vivadata SET TransactionId='".$trid."', Status='Viva return post' WHERE ordercode='".$ordercode."';";
$db->setQuery($query);
$db->query();

$w = 'SELECT * FROM `#__vivadata` WHERE `ordercode`="'.$ordercode.'" ';
$db->setQuery($w);
if (!($vivaTable = $db->loadObject())) {
	header('Location: ' . $redirecturl);
	exit;
}

if(isset($vivaTable->itemid) && $vivaTable->itemid!=''){
	$url_itemid='&Itemid='.$itemid;
} else {
	$url_itemid='';
}

if(isset($vivaTable->locale) && $vivaTable->locale!=''){
	$url_lang='&lang='.substr($vivaTable->locale, 0, 2);
} else {
	$url_lang='';
}


if(preg_match("/success/i", $_SERVER['REQUEST_URI']) && preg_match("/viva/i", $_SERVER['REQUEST_URI'])){
$status = 'success';
$redirecturl = $Protocol.$URL.'index.php?option=com_k2store&view=checkout&task=confirmPayment&orderpayment_type=payment_viva&paction=success_payment'.$url_ref.$url_itemid.$url_lang;
} else {
$status = 'fail';
$redirecturl = $Protocol.$URL.'index.php?option=com_k2store&view=checkout&task=confirmPayment&orderpayment_type=payment_viva&paction=cancel_payment'.$url_ref.$url_itemid.$url_lang;
}

$query = "UPDATE #__vivadata SET Status='".$status."' WHERE ordercode='".$ordercode."';";
$db->setQuery($query);
$db->query();

}


header('Location: ' . $redirecturl);
exit;

?>