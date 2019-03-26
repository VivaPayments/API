<?php
/**
 * @package	HikaShop for Joomla!
 * @version	1.0.0
 * @author	Viva Wallet
 * @copyright	(C) 2017 Vivawallet.
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashoppaymentViva extends JPlugin
{
	var $accepted_currencies = array
	(
		'EUR','GBP','RON','BGN'
	);

	var $debugData = array();

	function onPaymentDisplay(&$order, &$methods, &$usable_methods)
	{
		if(!empty($methods))
		{
			foreach($methods as $method)
			{
				if($method->payment_type != 'viva' || !$method->enabled)
				{
					continue;
				}
				if(!empty($method->payment_zone_namekey))
				{
					$zoneClass = hikashop_get('class.zone');
					$zones = $zoneClass->getOrderZones($order);
					if(!in_array($method->payment_zone_namekey, $zones))
					{
						return true;
					}
				}
				$currencyClass = hikashop_get('class.currency');
				$null = null;
				if(!empty($order->total))
				{
					$currency_id = intval(@$order->total->prices[0]->price_currency_id);
					$currency = $currencyClass->getCurrencies($currency_id, $null);
					if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code, $this->accepted_currencies))
					{
						return true;
					}
				}
				
				//instalments
				$this->loadLanguage('plg_hikashoppayment_viva_hikashop');
				$vivar_total_eur = $order->total->prices[0]->price_value_with_tax;
				$instal_logic = trim($method->payment_params->instal);
				if(isset($instal_logic) && $instal_logic !=''){
				$split_instal_viva = explode(',', $instal_logic);
				$c = count ($split_instal_viva);
				
				$instal_viva = '';
				$instal_viva .= '<table width="100%"><tr><td><select name="plg_viva_instal">'."\n";
				$instal_viva .= '<option value="">'.JText::_('PLG_VIVA_SELECT_INSTAL').'</option>'."\n";
				$instal_viva .= '<option value="">'.JText::_('PLG_VIVA_NO_INSTAL').'</option>'."\n";
			
				for($i=0; $i<$c; $i++)
				{
				list($instal_amount, $instal_term) = explode(":", $split_instal_viva[$i]);
				
				if($vivar_total_eur >= $instal_amount){
				$instal_viva .= '<option value="'.$instal_term.'">'. $instal_term . JText::_('PLG_VIVA_INSTALMENTS').'</option>'."\n"; 
				}
				}
				$instal_viva .= '</select><br><br></td></tr></table>'."\n";
				} else {
				$instal_viva = '';
				}				
				$method->payment_description .= $instal_viva;
				
				$usable_methods[$method->ordering] = $method;
			}
		}
		return true;
	}

	function onPaymentSave(&$cart, &$rates, &$payment_id)
	{
		$_SESSION['plg_viva_instal']=@$_REQUEST['plg_viva_instal'];
		$usable = array();
		$this->onPaymentDisplay($cart, $rates, $usable);
		$payment_id = (int)$payment_id;
		foreach($usable as $usable_method)
		{
			if($usable_method->payment_id == $payment_id)
			{
				return $usable_method;
			}
		}
		
		
		return false;
	}

	function getVars($order, $methods, $method_id)
	{
		global $Itemid;

		$method =  &$methods[$method_id];
		$currencyClass = hikashop_get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id, $currencies);
		$currency = $currencies[$order->order_currency_id];
		hikashop_loadUser(true,true);
		$user = hikashop_loadUser(true);
		$lang = JFactory::getLanguage();
		$locale = $lang->get('tag');
		$mref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
		$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
		
		if( isset($method->payment_params->merchantidsec) && $method->payment_params->merchantidsec!='' && isset($method->payment_params->merchantidsecloc) && strtoupper($method->payment_params->merchantidsecloc)==strtoupper($locale) ){
		$mcode = $method->payment_params->merchantidsec;
		} else {
		$mcode = $method->payment_params->merchantid;
		}
		
		$tramount =  preg_replace('/,/', '.', $order->order_full_price);
		$tramount = number_format($tramount, 2, '.', '');
		$tramountformat = round($tramount * 100);
		
		$currency_symbol ='';
		$currency_code = $currency;
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
		
		if (preg_match("/gr/i", $locale)) {
		$formlang = 'el-GR';
		} else {
		$formlang = 'en-US';
		}
		
		if(isset($_SESSION['plg_viva_instal']) && $_SESSION['plg_viva_instal'] > 0){
		$period = intval($_SESSION['plg_viva_instal']);
		} else {
		$period = '1';
		}
		
		$postargs = 'Amount='.urlencode($tramountformat).'&RequestLang='.urlencode($formlang).'&Email='.urlencode($user->user_email).'&MaxInstallments='.urlencode($period).'&MerchantTrns='.urlencode($order->order_id).'&SourceCode='.urlencode($mcode).'&CurrencyCode='.urlencode($currency_symbol).'&PaymentTimeOut=300';
		
		$curl = curl_init("https://www.vivapayments.com/api/orders");
		curl_setopt($curl, CURLOPT_PORT, 443);
		
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $method->payment_params->user.':'.html_entity_decode($method->payment_params->pass));  
		$curlversion = curl_version();
		if(!preg_match("/NSS/" , $curlversion['ssl_version'])){
		curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, "TLSv1");
		}

		$response = curl_exec($curl);
		
		if(curl_error($curl)){
		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $method->payment_params->user.':'.html_entity_decode($method->payment_params->pass));
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

		$vars = array
		(
			"Ref" => $OrderCode
		);
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('`#__vivadata`');
		$query->columns('`ref`,`orderid`,`ordercode`, `total_cost`, `locale`, `period`, `itemid`, `currency`, `order_state`, `timestamp`');
		$query->values('"'.$mref.'","'.$order->order_id.'","'.$OrderCode.'","'.$tramount.'","'.$locale.'","'.$period.'","'.$Itemid.'","'.$currency_code.'","I",now()');
		$db->setQuery($query);
		$db->execute();
		
		$cleandb_query = "DELETE from #__vivadata where (to_days(now())- to_days(timestamp)) > 180";
		$db->setQuery($cleandb_query);
		$db->query();

		return $vars;
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id)
	{
		$method =  & $methods[$method_id];
		$tax_total = '';
		$discount_total = '';

		$vars = $this->getVars($order, $methods, $method_id);

		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');

		$app = JFactory::getApplication();
		$name = $method->payment_type . '_end.php';
		$path = JPATH_THEMES . DS . $app->getTemplate() . DS . 'hikashoppayment' . DS . $name;
		
		$plg_viva_url = 'https://www.vivapayments.com/web/newtransaction.aspx';
		
		if(!file_exists($path))
		{
			if(version_compare(JVERSION, '1.6', '<'))
			{
				$path = JPATH_PLUGINS . DS . 'hikashoppayment' . DS . $name;
			}
			else
			{
				$path = JPATH_PLUGINS . DS . 'hikashoppayment' . DS . $method->payment_type . DS . $name;
			}
			if(!file_exists($path))
			{
				return true;
			}
		}

		require($path);
		return true;
	}

	function onPaymentNotification(&$statuses)
	{

		$pluginsClass = hikashop_get('class.plugins');
		$elements = $pluginsClass->getMethods('payment', 'viva');

		if(empty($elements))
			return false;

		$element = reset($elements);
		
		//fail
		if(preg_match("/vivfail/i", $_SERVER['REQUEST_URI']) && preg_match("/viva/i", $_SERVER['REQUEST_URI']) && isset($_GET['s']))
		{
		$tm_ref = addslashes($_GET['s']);
		
		$db = JFactory::getDBO();
		$query = "UPDATE #__vivadata SET order_state = 'F' WHERE ordercode='".$tm_ref."';";
		$db->setQuery($query);
		$db->query();
		
		$db->setQuery("SELECT orderid, locale, itemid FROM #__vivadata WHERE ordercode='".$tm_ref."';");
		$check_query = $db->loadObjectList();
		$id = $check_query[0]->orderid;
		$itemid = @$check_query[0]->itemid;
		$locale=strtolower(substr(@$check_query[0]->locale,0,2));
		
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get($id);

		$order = new stdClass();
		$order->order_id = $dbOrder->order_id;

		$url = HIKASHOP_LIVE . 'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order->order_id;
		$order_text = "\r\n" . JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n" . str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));

		$mailer = JFactory::getMailer();
		$config =  & hikashop_config();
		$sender = array($config->get('from_email'), $config->get('from_name'));

		$mailer->setSender($sender);
		$mailer->addRecipient($config->get('from_email'));

		$this->loadLanguage('plg_hikashoppayment_viva_hikashop');
		$order->order_status = 'cancelled';
		$order->history->history_reason = JText::_(PLG_VIVA_FAIL);
		$order->history->history_notified = 0;
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method = $element->payment_type;
		$order->history->history_data = "Payment by Viva Failed OrderCode: " . $tm_ref;
		$order->history->history_type = 'payment';
		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', 'Viva') . 'invalid response');
		$body = JText::sprintf("Hello,\r\n A Viva payment has failed") . "\r\n\r\n" . $order_text;
		$mailer->setBody($body);
		$mailer->Send();
		$orderClass->save($order);
		
		if(!empty($itemid)){
			$url_itemid='&Itemid='.$itemid;
		} else {
			$url_itemid='';
		}

		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.'&lang='.$locale.$url_itemid;
		
		$app =& JFactory::getApplication();
		$app->enqueueMessage(JText::_('PLG_VIVA_FAIL'));
		$app->redirect($cancel_url);

		exit;
	  }
			
		//success
		if(preg_match("/vivok/i", $_SERVER['REQUEST_URI']) && preg_match("/viva/i", $_SERVER['REQUEST_URI']) && isset($_GET['s']))
		{
		
		$tm_ref = addslashes($_GET['s']);
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT orderid, locale, itemid FROM #__vivadata WHERE ordercode='".$tm_ref."';");
		$check_query = $db->loadObjectList();
		$check_query_count = count($check_query);
		if($check_query_count >= 1){
		
		$query = "UPDATE #__vivadata SET order_state = 'P' WHERE ordercode='".$tm_ref."';";
		$db->setQuery($query);
		$db->query();
			
		$id = $check_query[0]->orderid;
		$itemid = @$check_query[0]->itemid;
		$locale=strtolower(substr(@$check_query[0]->locale,0,2));
		
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get($id);

		$order = new stdClass();
		$order->order_id = $dbOrder->order_id;

		$url = HIKASHOP_LIVE . 'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order->order_id;
		$order_text = "\r\n" . JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n" . str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));

		$mailer = JFactory::getMailer();
		$config =  & hikashop_config();
		$sender = array($config->get('from_email'), $config->get('from_name'));

		$mailer->setSender($sender);
		$mailer->addRecipient($config->get('from_email'));

		$order->order_status = $element->payment_params->verified_status;
		$order->history->history_reason = JText::_('PAYMENT_ORDER_CONFIRMED');
		$order->history->history_notified = 1;
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method = $element->payment_type;
		$order->history->history_data = "Payment by Viva - OrderCode: " . $tm_ref;
		$order->history->history_type = 'payment';
		$order->mail_status = $statuses[$order->order_status];
		$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Viva', $order->mail_status, $dbOrder->order_number));
		$body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Viva', $order->mail_status)) . ' ' . JText::sprintf('ORDER_STATUS_CHANGED', $order->mail_status) . "\r\n\r\n" . $order_text;
		$mailer->setBody($body);
		$mailer->Send();
		$orderClass->save($order);
		
		if(!empty($itemid)){
			$url_itemid='&Itemid='.$itemid;
		} else {
			$url_itemid='';
		}

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.'&lang='.$locale.$url_itemid;
		
		$app =& JFactory::getApplication();
		$app->redirect($return_url);

		exit;
	  }	
	 }	
	
	}

	function onPaymentConfiguration(&$element)
	{
		$this->viva = JRequest::getCmd('name', 'viva');

		if(empty($element))
		{
			$element = new stdClass();
			$element->payment_name = 'Viva Payments';
			$element->payment_description = 'You can pay by credit card using this payment method';
			$element->payment_type = $this->viva;
			$element->payment_params = new stdClass();
			$element->payment_params->notification = 1;
			$element->payment_params->invalid_status = 'cancelled';
			$element->payment_params->pending_status = 'created';
			$element->payment_params->verified_status = 'confirmed';
			$element = array($element);
		}

		$this->toolbar = array
		(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => 'payment-viva-form')
		);

		hikashop_setTitle('Viva', 'plugin', 'plugins&plugin_type=payment&task=edit&name=' . $this->viva);
		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT . '.payment_plugin_type', $this->viva);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}

}
