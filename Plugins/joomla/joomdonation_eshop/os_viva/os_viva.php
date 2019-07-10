<?php

defined('_JEXEC') or die();

class os_viva extends os_payment
{
	/**
	 * Constructor functions, init some parameter
	 *
	 * @param object $config        	
	 */
	public function __construct($params)
	{
        $config = array(
            'type' => 0,
            'show_card_type' => false,
            'show_card_holder_name' => false
        );

        parent::__construct($params, $config);
		
		$db = JFactory::getDBO();
		$db->setQuery("CREATE TABLE IF NOT EXISTS `#__vivadata` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `ref` varchar(150) NULL,
		  `oid` varchar(150) NULL,
		  `total_cost` varchar(50) NULL,
		  `locale` varchar(50) NULL,
		  `email` varchar(255) NULL,
		  `currency` char(3) NULL,
		  `order_state` char(1) NULL,
		  `timestamp` datetime default null,
		  `txid` varchar(255) NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$db->query();

	}
	/**
	 * Process Payment
	 *
	 * @param array $params        	
	 */
	public function processPayment($data)
	{
		$siteUrl = JUri::root();
		$countryInfo = EshopHelper::getCountry($data['payment_country_id']);
		
		$lang = JFactory::getLanguage();
		$locale = $lang->get('tag');
		
		if($locale=='el_GR'){
		$this->formlang = 'el-GR';
		} else {
		$this->formlang = 'en-US';
		}
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT params FROM #__eshop_payments WHERE name='os_viva';");
		$param_query = $db->loadObjectList();
		$params = new JRegistry;
		$params->loadString($param_query[0]->params);
		
		$currency_symbol ='';
		$currency_code = $data['currency_code'];
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
		
		$MerchantID =  trim($params->get('viva_mid'));
		$Password =   trim(html_entity_decode($params->get('viva_pass')));
		$Source =   trim($params->get('viva_source'));
		$instalments =  trim($params->get('viva_instalments'));

		$customer_mail = $data['email'];
		$oid = $data['order_id'];
		$tramount =  preg_replace('/,/', '.', $data['total']);
		$charge = number_format($tramount, 2, '.', '');
		$amountcents = round($charge * 100);
		
		$poststring = array();
		$poststring['Amount'] = $amountcents;
		$poststring['RequestLang'] = $this->formlang;
		
		$poststring['Email'] = $customer_mail;
		if(isset($instalments) && $instalments > 0){
		$poststring['MaxInstallments'] = $instalments;
		} else {
		$poststring['MaxInstallments'] = '1';
		}
		$poststring['MerchantTrns'] = $oid;
		$poststring['SourceCode'] = $Source;
		$poststring['CurrencyCode'] = $currency_symbol;

		
		$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($poststring['MaxInstallments']).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&CurrencyCode='.urlencode($poststring['CurrencyCode']).'&PaymentTimeOut=300';
		
		$curl = curl_init("https://www.vivapayments.com/api/orders");
		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
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
		curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
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
		
		$query = $db->getQuery(true);
		$query->insert('`#__webitmodirumdata`');
		$query->columns('`ref`,`oid`,`total_cost`, `locale`, `email`, `currency`, `order_state`, `timestamp`');
		$query->values('"'.$OrderCode.'","'.$oid.'","'.round($data['total'],2).'","'.$locale.'","'.$customer_mail.'","'.$currency_symbol.'","I",now()');
		$db->setQuery($query);
		$db->execute();

		$this->url = 'https://www.vivapayments.com/web/newtransaction.aspx?Ref='.$OrderCode;
		$this->setData('Ref', $OrderCode);
		
		$this->submitPost();
	}

	/**
	 * Process payment
	 */
	public function verifyPayment()
	{
		$ret = true;
		$currency = new EshopCurrency();
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT params FROM #__eshop_payments WHERE name='os_viva';");
		$param_query = $db->loadObjectList();
		$params = new JRegistry;
		$params->loadString($param_query[0]->params);
		
		isset($_GET['s']) ? $ordercode = addslashes($_GET['s']) : $ordercode = '';
		isset($_GET['t']) ? $txid = addslashes($_GET['t']) : $txid = '';

		$MerchantID =  trim($params->get('viva_mid'));
		$Password 	=  trim($params->get('viva_pass'));
		
		if (isset($ordercode) && $ordercode!=''){
		
		$postargs = 'https://www.vivapayments.com/api/transactions/';
		$postargs .= $txid;
		$OrderStatus = "";
		$OrderAmount = "";
		
		if (isset($txid) && $txid!=''){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_URL, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
		$curlversion = curl_version();
		if(!preg_match("/NSS/" , $curlversion['ssl_version'])){
		curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, "TLSv1");
		}
		
		// execute curl
		$response = curl_exec($curl);
		
		if(curl_error($curl)){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_URL, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		}
		
	
		curl_close($curl);
		
			try {
			$resultObj=json_decode($response);
			} catch( Exception $e ) {
				throw new Exception("Result is not a json object (" . $e->getMessage() . ")");
			}
			
			if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
			$OrderStatus = $resultObj->Transactions[0]->StatusId;
			$OrderCode = $resultObj->Transactions[0]->Order->OrderCode;
			$OrderAmount = number_format($resultObj->Transactions[0]->Amount, 2, '.', '');
			}
			else{
				throw new Exception("Unable to create order code (" . $resultObj->ErrorText . ")");
			}
		}	
		
		
			$db->setQuery("SELECT * FROM #__vivadata WHERE ref='".$ordercode."';");
			$check_query = $db->loadObjectList();
			$id = $check_query[0]->oid;
			
			$row = JTable::getInstance('Eshop', 'Order');
			$siteUrl = JUri::root();
			$amount = $OrderAmount;
			$trstatus = 'ok';
			
			if ($amount < 0)
				$trstatus = 'fail';
			$row->load($id);
			if ($row->order_status_id == EshopHelper::getConfigValue('complete_status_id'))
				$trstatus = 'fail';
			if($OrderStatus!='A' && $OrderStatus!='F')
				$trstatus = 'fail';
				
					
			if($trstatus=='ok'){
			$query = "UPDATE #__vivadata SET order_state = 'P', txid = '".$txid."' WHERE ref='".$ordercode."';";
			$db->setQuery($query);
			$db->query();
			
			$row->transaction_id = $txid;
			$row->order_status_id = EshopHelper::getConfigValue('complete_status_id');
			$row->store();
			EshopHelper::completeOrder($row);
			JPluginHelper::importPlugin('eshop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterCompleteOrder', array($row));
			//Send confirmation email here
			if (EshopHelper::getConfigValue('order_alert_mail')){
				EshopHelper::sendEmails($row);
			}
			header('Location: ' . $siteUrl . 'index.php?option=com_eshop&view=checkout&layout=complete');
			exit;
			} else {
			header('Location: ' . $siteUrl . 'index.php?option=com_eshop&view=checkout&layout=cancel&id=' . $id);
			exit;
			}

		}

	}
}
