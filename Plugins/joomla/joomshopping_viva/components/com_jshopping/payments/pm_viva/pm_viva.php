<?php
defined('_JEXEC') or die('Restricted access');

class pm_viva extends PaymentRoot{

    function showPaymentForm($params, $pmconfigs){
	JSFactory::loadExtLanguageFile('viva');
	if (!isset($params['period'])) $params['period'] = '';
	
	$cart = JModelLegacy::getInstance('cart', 'jshop');
	$cart->load();
	$cart->setDisplayItem(1, 1);
	$cart->setDisplayFreeAttributes();
	$order_total = $cart->getSum(1, 1, 1);
	$carttotal = number_format($order_total, 2, '.', '');
	  
		if( isset($pmconfigs['install']) && trim($pmconfigs['install'])!=''){
		$split_instal_logic = explode(',', $pmconfigs['install']);
		$c = count ($split_instal_logic);
		
		$installogic = '';
		$installogic .= '<table>
		<tr>
		<td width="200">'._JSHOP_VIVA_INSTALL_SELECT.'</td><td><select name="params[pm_viva][period]">'."\n";
		$installogic .= '<option value="0">'._JSHOP_VIVA_INSTALL_NO.'</option>'."\n";
		
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_term) = explode(":", $split_instal_logic[$i]);
		
		if($carttotal >= $instal_amount){
		$period_amount = round(($carttotal / $instal_term), 2);
		$period_amount = number_format($period_amount, 2, '.', '');
		$installogic .= '<option value="'.$instal_term.'">'. $instal_term . _JSHOP_VIVA_INSTALL_OPTION . '</option>'."\n"; 
		}
		}
		$installogic .= '</select></td></tr></table>';
		} else {
		$installogic = '';
		}
	
	
        include(dirname(__FILE__)."/paymentform.php");
    }

	//function call in admin

	function showAdminFormParams($params){
	JSFactory::loadExtLanguageFile('viva');
	  $array_params = array('merchantid', 'merchantpass', 'merchantsource', 'install', 'transaction_end_status', 'transaction_pending_status', 'transaction_failed_status');
	  foreach ($array_params as $key){
	  	if (!isset($params[$key])) $params[$key] = '';
	  } 
	  $orders = JModelLegacy::getInstance('orders', 'JshoppingModel');  //admin model
      include(dirname(__FILE__)."/adminparamsform.php");	  
	}

	function checkTransaction($pmconfigs, $order, $act){
        $jshopConfig = JSFactory::getConfig();
		JSFactory::loadExtLanguageFile('viva');
        
		isset($_GET['ebref']) ? $tm_ref = $_GET['ebref'] : $tm_ref = '';

		if(isset($tm_ref) && $tm_ref!=''){
		
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__vivadata WHERE ref='".addslashes($tm_ref)."';");
		$check_query = $db->loadObjectList();
	
		$db_order_state = $check_query[0]->order_state;
		$db_orderid = $check_query[0]->orderid;
		$db_ordercode = $check_query[0]->ordercode;
		
		if($db_order_state=='P'){
		 return array(1, '');
		} else {
		 return array(0, _JSHOP_VIVA_FAIL . ' Order ID: '.$order->order_id . ' txId: ' . $db_ordercode);
		}
		} else {
		return array(0, _JSHOP_VIVA_FAIL . ' Order ID: '.$order->order_id);
		}
        
	}
	

	function showEndForm($pmconfigs, $order){
        $jshopConfig = JSFactory::getConfig();	    
        $item_name = sprintf(_JSHOP_PAYMENT_NUMBER, $order->order_number);
		$lang = JFactory::getLanguage();
		$locale = $lang->get('tag');
		$mref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
		$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
		
		$params = unserialize($order->payment_params_data);
		if(isset($params['period']) && $params['period'] > 0){
		$empinstal = intval($params['period']);
		} else {
		$empinstal = '1';
		}
		
		$set_currency = 'EUR';
		$MerchantID   = $pmconfigs['merchantid'];
		$mpass        = html_entity_decode($pmconfigs['merchantpass']);
		$msource      = $pmconfigs['merchantsource'];
		$orderid      = $order->order_id;
		
		if (preg_match("/gr/i", $locale)) {
		$formlang = 'el-GR';
		} else {
		$formlang = 'en-US';
		}

        $host = "https://www.vivapayments.com/web/newtransaction.aspx";
        
        $uri = JURI::getInstance();        
        $liveurlhost = $uri->toString(array("scheme",'host', 'port'));
		
		$successurl   = $liveurlhost.SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_viva&ebref=".$mref);
		$failurl = $liveurlhost.SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_viva&ebref=".$mref);
		$gateway   = JURI::root() . "viva.php";
		
		$tramount =  preg_replace('/,/', '.', $order->order_total);
		$amountcents = round($tramount * 100);
		$customer_mail = $order->email;
		
	$curl = curl_init("https://www.vivapayments.com/api/orders");
	curl_setopt($curl, CURLOPT_PORT, 443);
	
	$postargs = 'Amount='.urlencode($amountcents).'&RequestLang='.urlencode($formlang).'&Email='.urlencode($customer_mail).'&MaxInstallments='.urlencode($empinstal).'&MerchantTrns='.urlencode($orderid).'&SourceCode='.urlencode($msource).'&PaymentTimeOut=300';
	
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$mpass);  
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
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$mpass);
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
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('`#__vivadata`');
		$query->columns('`ref`,`orderid`, `total_cost`, `locale`, `period`, `ordercode`, `errorcode`, `errortext`, `okurl`, `failurl`, `gatewayurl`, `currency`, `order_state`, `timestamp`');
		$query->values('"'.$mref.'","'.$order->order_id.'","'.$tramount.'","'.$locale.'","'.$empinstal.'","'.$OrderCode.'","'.$ErrorCode.'","'.$ErrorText.'","'.$successurl.'","'.$failurl.'","'.$host.'","EUR","I",now()');
		$db->setQuery($query);
		$db->execute();
		
		$cleandb_query = "DELETE from #__vivadata where (to_days(now())- to_days(timestamp)) > 180";
		$db->setQuery($cleandb_query);
		$db->query();
        ?>
        <html>
        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />            
        </head>
        <body>
        <form action="<?php echo $gateway; ?>" method="POST" id="paymentform" name="paymentform">
        <input type="hidden" name="Ref" value="<?php echo $OrderCode; ?>" />
        <input type="hidden" name="APACScommand" value="NewPayment" />
        <input type="hidden" name="merchantRef" value="<?php echo $mref; ?>" />
        </form>      
        <?php print _JSHOP_REDIRECT_TO_PAYMENT_PAGE?>
        <br>
        <script type="text/javascript">document.getElementById('paymentform').submit();</script>
        </body>
        </html>
        <?php
        die();
	}
    

    function getUrlParams($pmconfigs){
        $params = array(); 
		$tm_ref = JRequest::getVar("ebref");
		
		$db = JFactory::getDbo();
		$db->setQuery("SELECT orderid FROM #__vivadata WHERE ref='".addslashes($tm_ref)."';");
		$check_query = $db->loadObjectList();
		$oid = $check_query[0]->orderid;
		
        $params['order_id'] = $oid;
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = "1";
		
    return $params;
    }

}
?>