<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\VivaPayments\Model;

use Exception;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'paymentmethod';
    protected $_isInitializeNeeded = true;
    protected $_canRefund = true;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\Generic $session,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_session = $session;
        $this->_customerSession = $customerSession;
        $this->_paymentData = $paymentData;
        $this->_localeResolver = $localeResolver;
        $this->_urlBuilder = $urlBuilder;
        $this->_objectManager = $objectManager;

    }
	
	public function getsupportedCurrencyCodes()
    {
        return explode(",", $this->getConfigData('allowed_currency'));
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->getsupportedCurrencyCodes())) {
            return false;
        }
        return true;
    }	
	
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    public function getPostHTML($order, $storeId = null)
    {
        $mref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
        $TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ=';

        $charge = $order->getBaseGrandTotal();
        $amountcents = round($order->getBaseGrandTotal() * 100);
        $MerchantID = $this->getConfigData('merchantid');
        $Password =   $this->getConfigData('merchantpass');
        $trlang = $this->_localeResolver->getLocale();
        $billingAddress = $order->getBillingAddress();;
        $firstName = $billingAddress->getFirstname();
        $lastName = $billingAddress->getLastname();

        $vivapayments_url = $this->getConfigData('cgi_url');
		
		$currency_code = $order->getBaseCurrencyCode();
		
		$currency_symbol ='';
        $language_code ='';
        
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

        
        if ($trlang=='el-GR') {
            $formlang = 'el-GR';
        } else {
            $formlang = 'en-US';
        }
        $maxperiod = '1';
        $installogic = $this->getConfigData('Installments');
        if(isset($installogic) && $installogic!=''){
             $split_instal_hellaspay = explode(',',$installogic);
             $c = count($split_instal_hellaspay);   
             $instal_hellaspay_max = array();
             for($i=0; $i<$c; $i++){
                list($instal_amount, $instal_term) = explode(":", $split_instal_hellaspay[$i]);
                if($charge >= $instal_amount){
                    $instal_hellaspay_max[] = trim($instal_term);
                }
            }
            if(count($instal_hellaspay_max) > 0){
                $maxperiod = max($instal_hellaspay_max);
            }
        }
        $poststring['Email'] = $billingAddress->getEmail();
        $poststring['Phone'] = $billingAddress->getTelephone();
        $poststring['FullName'] = $firstName.' '.$lastName;
        $poststring['PaymentTimeOut'] = 86400;
        $poststring['RequestLang'] = $formlang;
        $poststring['MaxInstallments'] = $maxperiod;
        $poststring['AllowRecurring'] = true;
        $poststring['IsPreAuth'] = true;
        $poststring['Amount'] = $amountcents;
        $poststring['MerchantTrns'] = $order->getIncrementId();
		$poststring['CurrencyCode'] = $currency_symbol;
        $poststring['SourceCode'] = $this->getConfigData('merchantsource');

        $order_url = $this->getConfigData('order_url');

        $curl = curl_init($order_url);
        curl_setopt($curl, CURLOPT_PORT, 443);
        
        $postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($poststring['MaxInstallments']).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&CurrencyCode='.urlencode($poststring['CurrencyCode']).'&PaymentTimeOut='.urlencode($poststring['PaymentTimeOut']);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
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
            
        }catch( Exception $e ) {
            throw new Exception("Result is not a json object (" . $e->getMessage() . ")");
        }
        if ($resultObj->ErrorCode==0){
            $OrderCode = $resultObj->OrderCode;
            $ErrorCode = $resultObj->ErrorCode;
            $ErrorText = $resultObj->ErrorText;
        }
        else{
            throw new Exception("Unable to create order code (" . $resultObj->ErrorText . ")");
        }

        $vivapaymentsObj = $this->_objectManager->create('Ced\VivaPayments\Model\VivaPayments');
        $data = [
                    'ref'=> $mref, 
                    'ordercode'=>$OrderCode, 
                    'email_address'=>$billingAddress->getEmail(), 
                    'order_id'=> $order->getIncrementId(), 
                    'total_cost'=>$amountcents, 
                    'currency'=>$currency_symbol, 
                    'order_state'=> 'Pending_payment', 
                    'timestamp'=>date('Y-m-d H:i:s')
                ];
        $vivapaymentsObj->addData($data)->save();
        $args = array
            (
                'Ref' => $OrderCode,
            );


        foreach ($args as $key => $value)
        {
            $args_array[] = '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
        }

        $form ='<form action="'.$vivapayments_url.'" method="GET" id="vivapayment_form">'."\n".
        implode("\n", $args_array)."\n".'</form>';
        $html = '<html><body>';
        $html.= $form;
        $html.='<script type="text/javascript">document.getElementById("vivapayment_form").submit();</script>';
        $html.= '</body></html>';
        return $html;
    }

    public function getOrderPlaceRedirectUrl($storeId = null)
    {
        return $this->_getUrl('vivapayments/checkout/start', $storeId);
    }

    public function getSuccessUrl($storeId = null)
    {
        return $this->_getUrl('checkout/onepage/success', $storeId);
    }

    public function getCancelUrl($storeId = null)
    {
        return $this->_getUrl('checkout/onepage/failure', $storeId);
    }

	public function getCheckout()
    {
        return $this->_checkoutSession;
    }
	
	public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    protected function _getUrl($path, $storeId, $secure = null)
    {
        $store = $this->_storeManager->getStore($storeId);

        return $this->_urlBuilder->getUrl(
            $path,
            ['_store' => $store, '_secure' => $secure === null ? $store->isCurrentlySecure() : $secure]
        );
    }

    public function validate()
    {
        $data = $this->getInfoInstance();    
        if ($data instanceof \Magento\Sales\Model\Order\Payment) {
            $billing_Country = $data->getOrder()->getBillingAddress()->getCountryId();
			$currency_code = $data->getOrder()->getBaseCurrencyCode();
        } else {
            $billing_Country = $data->getQuote()->getBillingAddress()->getCountryId();
			$currency_code = $data->getQuote()->getBaseCurrencyCode();
        }

        if (!$this->canUseForCountry($billing_Country)) {
            throw new \Magento\Framework\Validator\Exception(__('Selected payment type is not allowed for billing country.'));
        }
		
        if (isset($currency_code) && $currency_code!='' && !in_array($currency_code, $this->getsupportedCurrencyCodes())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Selected currency code ('.$currency_code.') is not compatible.')
            );
        }
		
        return $this;
    }
}
