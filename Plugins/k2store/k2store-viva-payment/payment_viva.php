<?php 
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR.'/components/com_k2store/library/plugins/payment.php');
require_once (JPATH_SITE.'/components/com_k2store/helpers/utilities.php');
jimport('joomla.application.component.helper');

class plgK2StorePayment_viva extends K2StorePaymentPlugin

{
    /**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'payment_viva';
    var $login_id    = '';
    var $tran_key    = '';
    var $_isLog      = false;
	
    public function plgK2StorePayment_viva(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );

        $this->vimerchantid = trim(html_entity_decode($this->_getParam( 'vimerchantid' )));
		$this->vipassword   = trim($this->_getParam( 'vipassword' ));
		$this->visource     = trim($this->_getParam( 'visource' ));
		$this->viinstal	    = trim($this->_getParam( 'viinstal' ));
        $this->_k2version   = $this->getVersion();

	}	

    /**
     * Form in Confirm order page
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    public function _prePayment( $data )
    {
        
		if(isset($_GET['Itemid']) && $_GET['Itemid']!=''){
		$Itemid = $_GET['Itemid'];		
		} else {
		$Itemid = '';
		}
		
		// prepare the payment form
        $params = JComponentHelper::getParams('com_k2store');
        $vars = new JObject();
		$vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2store/tables');
        $order = JTable::getInstance('Orders', 'Table');
        $order->load($data['orderpayment_id']);
        $currency_values= $this->getCurrency($order);

        $vars->currency_code =$currency_values['currency_code'];
        $vars->orderpayment_amount = $this->getAmount($order->orderpayment_amount, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert']);
		
        $currency_code =$currency_values['currency_code'];
		$currency_symbol ='';
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

        $vars->orderpayment_type = $this->_element;

        $vars->cart_session_id = JFactory::getSession()->getId();
		$vars->viinstal = $this->viinstal;
		
		$lang = JFactory::getLanguage();
		$locale = $lang->get('tag');
		$mref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
		$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
		
		$tramount =  preg_replace('/,/', '.', $vars->orderpayment_amount);
		$tramount = number_format($tramount, 2, ',', '');
		$amountcents = round($vars->orderpayment_amount * 100);
		
		if (preg_match("/gr/i", $locale)) {
		$formlang = 'el-GR';
		} else {
		$formlang = 'en-US';
		}
		
		$gatewayurl = 'https://www.vivapayments.com/web/newtransaction.aspx';
		
		if(isset($data['plg_viva_instal']) && $data['plg_viva_instal'] > 1){
		$period = intval($data['plg_viva_instal']);
		$vivainstal = $period;
		} else {
		$vivainstal = '';
		$period = '';
		}

		$MerchantID = $this->vimerchantid;
		$Password =   $this->vipassword;
		$SourceCode =   $this->visource;
		$customer_mail = $data['orderinfo']['user_email'];
		
	$curl = curl_init("https://www.vivapayments.com/api/orders");
	curl_setopt($curl, CURLOPT_PORT, 443);
	
	$postargs = 'Amount='.urlencode($amountcents).'&RequestLang='.urlencode($formlang).'&Email='.urlencode($customer_mail).'&MaxInstallments='.urlencode($vivainstal).'&MerchantTrns='.urlencode($data['orderpayment_id']).'&SourceCode='.urlencode($SourceCode).'&CurrencyCode='.urlencode($currency_symbol).'&PaymentTimeOut=300';
	
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
		
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('`#__vivadata`');
		$query->columns('`mref`,`orderid`, `locale`, `period`, `ordercode`, `itemid`, `CurrencyCode`, `MerchantReference`, `Amount`, `Installments`, `Status`');
		$query->values('"'.$mref.'","'.$data['orderpayment_id'].'","'.$locale.'","'.$period.'","'.$OrderCode.'","'.$Itemid.'","'.$currency_code.","'.$mref.'","'.$tramount.'","'.$period.'","preorder"');
		$db->setQuery($query);
		$db->execute();
		
		$cleandb_query = "DELETE from #__vivadata where (to_days(now())- to_days(timestamp)) > 180";
		$db->setQuery($cleandb_query);
		$db->query();
		
        $vars->form_url = $gatewayurl;
		$vars->OrderCode = trim($OrderCode);

        //lets check the values submitted
        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    }

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    public function _postPayment( $data )
    {
        // Process the payment
        $vars = new JObject();

        $app =JFactory::getApplication();
        $paction = JRequest::getVar( 'paction' );
		$tm_ref = JRequest::getVar( 'tmref' );

        switch ($paction)
        {
            case "fail_payment":
			 $db = JFactory::getDBO();
			 $w = 'SELECT * FROM `#__vivadata` WHERE `ordercode`="'.$tm_ref.'"';
				$db->setQuery($w);
				
                // Check User loged in
                $user = JFactory::getUser();
                if($user->id > 0){
				
				if (($vivaTable = $db->loadObject())) {
				JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2store/tables');
				$tOrder = JTable::getInstance('Orders', 'Table');
				$tOrder->load($vivaTable->orderid);

				if(isset($tOrder->id) && $tOrder->user_id==$user->id){
					$tOrder->transaction_id       = $vivaTable->TransactionId;
					$tOrder->transaction_details  = $this->_getFormattedTransactionDetails($vivaTable);
	        	    $tOrder->transaction_status   = JText::_('K2STORE_FAILED');
					$tOrder->order_state 		  = JText::_('K2STORE_FAILED');
					$tOrder->order_state_id 	  = 3;
					$tOrder->store();
				}
				}

                $session = JFactory::getSession();
                $session->set('k2store_cart', array());
				
                $vars->message = JText::_( 'PLG_VIVA_FAIL' );
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();
				}			
              break;
			case "cancel_payment":
                $vars->message = JText::_( 'PLG_VIVA_FAIL' ) . '<br /><a href="'.JRoute::_('index.php?option=com_k2store&view=mycart').'" target="_self">'.JText::_('K2STORE_VIEW_CART').'</a>';
                $html = $this->_getLayout('message', $vars);
              break;  			
			case "display_message":
                $session = JFactory::getSession();
                $session->set('k2store_cart', array());
                $vars->message = JText::_($this->params->get('onafterpayment', ''));
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();

              break;
            case "success_payment":
			 $db = JFactory::getDBO();
			 $w = 'SELECT * FROM `#__vivadata` WHERE `ordercode`="'.$tm_ref.'"';
				$db->setQuery($w);
				if (!($vivaTable = $db->loadObject())) {
					$vars->message = JText::_( 'PLG_VIVA_FAIL' );
                	$html = $this->_getLayout('message', $vars);
					exit;
				}   
				
				
                // Check User loged in
                $user = JFactory::getUser();
                if($user->id > 0){
				
				JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2store/tables');
				$tOrder = JTable::getInstance('Orders', 'Table');
				$tOrder->load($vivaTable->orderid);

				if($vivaTable->Status=="success" && isset($tOrder->id) && $tOrder->user_id==$user->id){
					$tOrder->transaction_id       = $vivaTable->TransactionId;
					$tOrder->transaction_details  = $this->_getFormattedTransactionDetails($vivaTable);
	        	    $tOrder->transaction_status   = JText::_('K2STORE_COMPLETED');
					$tOrder->order_state 		  = JText::_('K2STORE_COMPLETED');
					$tOrder->order_state_id 	  = 1;
					$tOrder->store();
					
					require_once (JPATH_SITE.'/components/com_k2store/helpers/orders.php');
					K2StoreHelperCart::removeOrderItems( $tOrder->id );
					K2StoreOrdersHelper::sendUserEmail($user->id,$tOrder->order_id, $tOrder->transaction_status, $tOrder->order_state, $tOrder->order_state_id);
				}

                $session = JFactory::getSession();
                $session->set('k2store_cart', array());
				
                $vars->message = JText::_( 'PLG_VIVA_SUCCESS' );
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();
				}
              break;
            default:
                $vars->message = JText::_( 'PLG_VIVA_FAIL' );
                $html = $this->_getLayout('message', $vars);
              break;
        }

        return $html;
    }

    /**
     * Prepares variables and
     * Renders the form for collecting payment info
     *
     * @return unknown_type
     */
    public function _renderForm( $data )
    {
        
		$img_url = JROUTE::_ (JURI::root () . 'plugins/k2store/payment_viva/payment_viva/logo.png');
		
		$app =JFactory::getApplication();
		$orderpayment_id = (int) $app->getUserState( 'k2store.orderpayment_id' );
		
		$order = JTable::getInstance('Orders', 'Table');
        $order->load($orderpayment_id);
        $currency_values= $this->getCurrency($order);
		
        $currency_code =$currency_values['currency_code'];
        $orderpayment_amount = $this->getAmount($order->orderpayment_amount, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert']);
		
		$vars = new JObject();
		
		$instal_viva_show = 'no';
		$viva_total_eur = $orderpayment_amount;
		$instal_logic = trim($this->viinstal);
		if(isset($instal_logic) && $instal_logic !=''){
		$split_instal_viva = explode(',', $instal_logic);
		$c = count ($split_instal_viva);
		
		$instal_viva = '';
	
		$types[] = JHTML::_('select.option', "", JText::_( "PLG_VIVA_SELECT_INSTAL" ) );
		$types[] = JHTML::_('select.option', "", JText::_( "PLG_VIVA_NO_INSTAL" ) );
	
		for($i=0; $i<$c; $i++)
		{
		list($instal_amount, $instal_term) = explode(":", $split_instal_viva[$i]);
		
		if($viva_total_eur >= $instal_amount){
		$instal_viva_show = 'yes';
		$types[] = JHTML::_('select.option', $instal_term, $instal_term . JText::_( "PLG_VIVA_INSTALMENTS" ) );
		}
		}
		$instal_viva .= '<ul style="list-style:none;">
		<li><img src="'.$img_url.'" alt="Viva Payments"><br /><br /></li>
		<li>'.JHTML::_('select.genericlist', $types, "plg_viva_instal", '', 'value','text', '').'</li></ul>';
		} 			
		
		if($instal_viva_show == 'yes'){
		$vars->viinstal = $instal_viva;
		} else {
		$vars->viinstal = '<ul style="list-style:none;">
		<li><img src="'.$img_url.'" alt="Viva Payments"></li>
		</ul>';
		}

        $html = $this->_getLayout('form', $vars);

        return $html;
    }

    /**
     * Verifies that all the required form fields are completed
     * if any fail verification, set
     * $object->error = true
     * $object->message .= '<li>x item failed verification</li>'
     *
     * @param $submitted_values     array   post data
     * @return unknown_type
     */
    public function _verifyForm( $submitted_values )
    {
        $jinput = JFactory::getApplication()->input;
        $object = new JObject();
        $object->error = false;
        $object->message = '';

        return $object;
    }

    public function getCurrency($order) {
		$results = array();
		$convert = false;
		$params = JComponentHelper::getParams('com_k2store');
    	if( version_compare( $this->_k2version, '3.7.3', 'lt' ) ) {
    		$currency_code = $params->get('currency_code', 'EUR');
    		$currency_value = 1;
    	} else {

    		include_once (JPATH_ADMINISTRATOR.'/components/com_k2store/library/base.php');
    		$currencyObject = K2StoreFactory::getCurrencyObject();

    		$currency_code = $order->currency_code;
    		$currency_value = $order->currency_value;

    		//accepted currencies
    		$currencies = $this->getAcceptedCurrencies();
    		if(!in_array($order->currency_code, $currencies)) {
    			$default_currency = 'EUR';
    			if($currencyObject->has($default_currency)) {
    				$currencyObject->set($default_currency);
    				$currency_code = $default_currency;
    				$currency_value = $currencyObject->getValue($default_currency);
    				$convert = true;
    			}
    		}
    	}
    	$results['currency_code'] = $currency_code;
    	$results['currency_value'] = $currency_value;
    	$results['convert'] = $convert;

    	return $results;
    }
	
    public function getAmount($value, $currency_code, $currency_value, $convert=false) {

    	if( version_compare( $this->_k2version, '3.7.3', 'lt' ) ) {
    		return K2StoreUtilities::number( $value, array( 'thousands'=>'', 'num_decimals'=>'2', 'decimal'=>'.') );
    	} else {
    		include_once (JPATH_ADMINISTRATOR.'/components/com_k2store/library/base.php');
    		$currencyObject = K2StoreFactory::getCurrencyObject();
    		$amount = $currencyObject->format($value, $currency_code, $currency_value, false);
    		return $amount;
    	}

    }
		
	public function getVersion() {
	if(!isset($this->_k2version)) {
		$xmlfile = JPATH_ADMINISTRATOR.'/components/com_k2store/manifest.xml';
		$xml = JFactory::getXML($xmlfile);
		$this->_k2version=(string)$xml->version;
	}
	return $this->_k2version;
	}
	
    public function getAcceptedCurrencies() {
    	$currencies = array('EUR','GBP','RON','BGN');
    	return $currencies;
    }	
	
    public function _getParam($name, $default = '')
    {
            $param = $this->params->get($name, $default);

        return $param;
    }	

    public function _getFormattedTransactionDetails( $data )
    {
        $separator = "\n";
        $formatted = array();

        foreach ($data as $key => $value)
        {
            if ($key != 'view' && $key != 'layout')
            {
                $formatted[] = $key . ' = ' . $value;
            }
        }

        return count($formatted) ? implode("\n", $formatted) : '';
    }	
}