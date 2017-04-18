<?php

class Mage_Hellaspay_Model_Hpay extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'hpay';
	//invoice_pending
	protected $_canCapture = true; 
	protected $_canCapturePartial = true; 
	protected $_canUseForMultishipping = false;
	
	protected $_formBlockType = 'hellaspay/form_cc';
	protected $_hpokBlockType = 'hellaspay/hpay_hpok';
	protected $_hpnokBlockType = 'hellaspay/hpay_hpnok';
	
    protected $_allowCurrencyCode = array('EUR');
	protected $_isInitializeNeeded = true; //no invoice 06/2013

	//no invoice 06/2013
	public function initialize($paymentAction, $stateObject)
    {
         $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
		//$state = Mage_Sales_Model_Order::STATE_CANCELED; //set to canceled by default
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
		//$stateObject->setStatus('canceled'); //set to canceled by default
        $stateObject->setIsNotified(false);	
    }
	
	public function getLogPath()
	{
		return Mage::getBaseDir() . '/var/log/hp.log';
	}
	public function getDebug(){
		return $this->getConfigData('debug');
	}
	public function getCurrency()
    {
        return $this->getConfigData('Currency');
    }
	public function getReference(){
		if($this->moduleReference == null)
		$MRef = microtime();
		$MRef1 = explode(" ", $MRef);
		$MRef2 = $MRef1[0].$MRef1[1];
		$MerchantRef = substr($MRef2, 2);
			$this->moduleReference = $MerchantRef;
			
		return $this->moduleReference;
	}
	public function getInstallments(){
		if($this->moduleInstallments == null)
		$this->moduleInstallments = $this->getConfigData('Installments');
		return $this->moduleInstallments;
	}
	public function getEncryptionKey()
    {
        return $this->getConfigData('d2ViaXQuYnovbGljZW5zZS50eHQ=');
    }
	public function getAllowedCurrency()
    {
        return $this->getConfigData(explode(",", Currency));
    }
	public function getOrderStatus()
    {
        return $this->getConfigData('order_status');
    }
    public function getMerchantid()
    {
        return $this->getConfigData('merchantid');
    }
	
	public function getMerchantpass()
    {
        return html_entity_decode($this->getConfigData('merchantpass'));
    }
	
	public function getSource()
    {
        return $this->getConfigData('merchantsource');
    }
	
	public function getSecsource()
    {
        
		$secsource = $this->getConfigData('merchantsecsource');
		if(isset($secsource) && $secsource!=''){
		$this->source = $secsource;
		} else {
		$this->source = $this->getConfigData('merchantsource');
		}
		
		return $this->source;
    }
	
	public function getOrderUrl()
    {
        return $this->getConfigData('order_url');
    }
	
	public function getUrl()
    {
        return $this->getConfigData('cgi_url');
    }		
	
    public function getSession()
    {
        return Mage::getSingleton('hellaspay/hpay_session');
    }

    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
	public function getCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }	
    public function getEmail()
    {
    	return (string)Mage::getSingleton('customer/session')->getCustomer()->getEmail();
    }

    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
		
	public function addHellaspayFields($form){

	$lastIncrementId = $this->_getCheckout()->getLastRealOrderId();
	$order = Mage::getModel('sales/order');
    $order->loadByIncrementId($lastIncrementId);
			
	$order_id = $lastIncrementId;
	$order_id_long = $lastIncrementId;
	$currency_code = $order->getBaseCurrencyCode();
	$locale_code = Mage::app()->getLocale()->getLocaleCode();
	
	
	$billing = $order->getBillingAddress();
    if (!empty($billing)) {
	$hpfcustomer = $billing->getFirstname();
	$hpcustomer = $billing->getLastname();
	$hpcustomermail = $order->getCustomerEmail();
	}
	
		$currency_symbol ='';
		$language_code ='';
		
		switch ($currency_code) {
		case 'EUR':
   		$currency_symbol = 978;
   		break;
		default:
        $currency_symbol = 978;
		}
		
		if($locale_code == 'el_GR'){
		$language_code = 'el-GR';
		$vivasource =  $this->getSource();
		} else {
		$language_code = 'en-US';
		$vivasource =  $this->getSecsource();
		}
		
		$amount = $order->getBaseGrandTotal();
		$amount =  preg_replace('/,/', '.', $amount);
		$amount_cents = sprintf("%.0f", $amount * 100);
		
		if($_SESSION['installments'] > 1){
		$instal = $_SESSION['installments'];
		} else {
		$instal = '1';
		}
		
		$MerchantID = $this->getMerchantid();
		$Password =  $this->getMerchantpass();
		
		$poststring['Amount'] = $amount_cents;
		$poststring['RequestLang'] = $language_code;
		
		$poststring['Email'] = $hpcustomermail;
		$poststring['MaxInstallments'] = $instal;
		$poststring['MerchantTrns'] = $order_id;
		$poststring['SourceCode'] = $vivasource;
		$poststring['PaymentTimeOut'] = '300';
	
		
		$curl = curl_init($this->getOrderUrl());
		if (preg_match("/https/i", $this->getOrderUrl())) {
		curl_setopt($curl, CURLOPT_PORT, 443);
		}
		
		$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($poststring['MaxInstallments']).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&PaymentTimeOut=300';
		
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

		$form->addField("Ref", 'hidden', array('name' => 'Ref', 'value' => $OrderCode));
		return $form;
	} 
 
 	//invoice_pending
	public function processInvoice($invoice, $payment)
    {
        $payment->setForcedState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);
        return $this;
    }
	
    public function createFormBlock($name)
    {
        
		$block = $this->getLayout()->createBlock('hellaspay/hpay_form', $name)
            ->setMethod('hpay')
            ->setPayment($this->getPayment())
            ->setTemplate('hellaspay/hpay/form.phtml');

        return $block;
    }

    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (isset($currency_code) && $currency_code!='' && !in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('hellaspay')->__('Selected currency code ('.$currency_code.') is not compatible'));
        }
		$postData = Mage::app()->getRequest()->getPost();
		if (isset($postData['cc_installments']) && $postData['cc_installments']!='') {
		$this->moduleInstallmentsPassed = $postData['cc_installments'];
		$_SESSION['installments'] = $this->moduleInstallmentsPassed;
		} else {
		$_SESSION['installments'] = '0';
		}
        
		return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
       return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {

    }

    public function canCapture()
    {
        return true;
    }

    public function getOrderPlaceRedirectUrl()
    {
		  return Mage::getUrl('hellaspay/hpay/redirect', array('_secure' => true)); //ssl
    }
    
    public function isAvailable($quote = null)
	{
		if($this->getDebug())
		{
	    	$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("entering isAvailable()");
		}
	
		if( $this->getConfigData('active') == 1){
		return true;
		}
		
		// Default, restrict access
		return false;
	}
}