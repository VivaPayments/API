<?php
declare(strict_types=1);  //mag23

namespace Ced\VivaPayments\Controller\Viva;

use Magento\Framework\App\CsrfAwareActionInterface; //mag23
use Magento\Framework\App\Request\InvalidRequestException; //mag23
use Magento\Framework\App\RequestInterface; //mag23
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Action as AppAction;
use Exception;

class Callback extends AppAction implements CsrfAwareActionInterface  //mag23 (implements)
{
    /**
    * @var \Ced\VivaPayments\Model\PaymentMethod
    */
    protected $_paymentMethod;
	protected $_checkoutSession;
    protected $_resultPageFactory;

    /**
    * @var \Magento\Sales\Model\Order
    */
    protected $_order;

    /**
    * @var \Magento\Sales\Model\OrderFactory
    */
    protected $_orderFactory;

    /**
    * @var Magento\Sales\Model\Order\Email\Sender\OrderSender
    */
    protected $_orderSender;

    /**
    * @var \Psr\Log\LoggerInterface
    */
    protected $_logger;
	
	private $_messageManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Sales\Model\OrderFactory $orderFactory
    * @param \Ced\VivaPayments\Model\PaymentMethod $paymentMethod
    * @param Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    * @param  \Psr\Log\LoggerInterface $logger
    */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Sales\Model\OrderFactory $orderFactory,
    \Ced\VivaPayments\Model\PaymentMethod $paymentMethod,
    \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
	\Magento\Framework\Message\ManagerInterface $messageManager,	
    \Psr\Log\LoggerInterface $logger,
	\Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    	
        $this->_paymentMethod = $paymentMethod;
        $this->_orderFactory = $orderFactory;
        $this->_client = $this->_paymentMethod->getClient();
        $this->_orderSender = $orderSender;	
		$this->_messageManager = $messageManager;	
        $this->_logger = $logger;		
		$this->_checkoutSession = $checkoutSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        try {	
			$this->_success();
			$this->paymentAction();

        } catch (Exception $e) {
            return $this->_failure();
        }
    }

    public function getOrderId(){
        return $this->_objectManager->get('Magento\Checkout\Model\Session')->getLastRealOrderId();
    }
	
	protected function paymentAction()
	{
		$payment_order = $this->getRequest()->getParam('s');
		$transactionId = $this->getRequest()->getParam('t');
       
		$OrderCode = $payment_order;	
		$Lang = $this->getRequest()->getParam('lang');
        $order_id = $this->getOrderId();
        $update_order = $this->_objectManager->create('Ced\VivaPayments\Model\VivaPayments')->load($OrderCode, 'ordercode');
        $this->_loadOrder($order_id);

        $MerchantID = $this->_scopeConfig->getValue('payment/paymentmethod/merchantid',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $APIKey =  $this->_scopeConfig->getValue('payment/paymentmethod/merchantpass',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $request = $this->_scopeConfig->getValue('payment/paymentmethod/transaction_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		$getargs = '?ordercode='.urlencode($OrderCode);

		$session = curl_init($request);

		curl_setopt($session, CURLOPT_HTTPGET, true);
		curl_setopt($session, CURLOPT_URL, $request . $getargs);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_USERPWD, $MerchantID.':'.$APIKey);
		$curlversion = curl_version();
        if(!preg_match("/NSS/" , $curlversion['ssl_version'])){
            curl_setopt($session, CURLOPT_SSL_CIPHER_LIST, "TLSv1");
        }

		$response = curl_exec($session);
		curl_close($session);
		try {
				
			if(is_object(json_decode($response))){
			  	$resultObj=json_decode($response);
			}
		} catch( Exception $e ) {
			echo $e->getMessage();
		}

		if ($resultObj->ErrorCode==0){
			if(sizeof($resultObj->Transactions) > 0) {
				foreach ($resultObj->Transactions as $t){
					$TransactionId = $t->TransactionId;
					$Amount = $t->Amount;
					$StatusId = $t->StatusId;
					$CustomerTrns = $t->CustomerTrns ;
                    $message = "Transactions completed Successfully";
                    $update_order->setOrderState('paid')->save();
				}
			} else {
				$update_order->setOrderState('failed')->save();
				$message = 'No transactions found. Make sure the order code exists and is created by your account.';
			}
		} else {
            $update_order->setOrderState('failed')->save();
			$message = 'The following error occured: <strong>' . $resultObj->ErrorCode . '</strong>, ' . $resultObj->ErrorText;
		}
        
		if(isset($StatusId) && strtoupper($StatusId) == 'F')
		{	
    		
		//BOF Order Status
		$orderComment = 'Viva Confirmed Transaction<br />';
            	$orderComment .= 'TxID: '.$transactionId.'<br />';

		$newstatus =  $this->_scopeConfig->getValue('payment/paymentmethod/order_status',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if(!isset($newstatus) || $newstatus == ''){
                    $newstatus = 'pending';
            	}
			
		if($newstatus =='complete'){
                    $this->_order->setData('state', "complete");
                    $this->_order->setStatus("complete");
					$this->_order->setBaseTotalPaid($Amount); 
					$this->_order->setTotalPaid($Amount);
                    $history = $this->_order->addStatusHistoryComment($orderComment, false);
                    $history->setIsCustomerNotified(true);

                } else {
                    $newstate = $newstatus;
                    $this->_order->setData('state', $newstate);
                    $this->_order->setStatus($newstate);
					$this->_order->setBaseTotalPaid($Amount); 
					$this->_order->setTotalPaid($Amount);
                    $history = $this->_order->addStatusHistoryComment($orderComment, false);
                    $history->setIsCustomerNotified(true);
                }
		//EOF Order Status
		
		$this->_order->setCanSendNewEmailFlag(true)->setEmailSent(true)->save();
		$this->_orderSender->send($this->_order, true);
			
		$this->_registerPaymentCapture($TransactionId, $Amount, $message);
    		$redirectUrl = $this->_paymentMethod->getSuccessUrl();
    		$this->_redirect($redirectUrl);
		}
		else
		{
			
			$checkoutHelper = $this->_objectManager->create('Ced\VivaPayments\Helper\Checkout');
			$checkoutHelper->cancelCurrentOrder($message);
        	//https://github.com/magento/magento2/pull/12668/commits/2c1d6a4d115f1e97787349849d215e6c73ac1335
			$checkoutHelper->restoreQuote();
			
			$this->_messageManager->addErrorMessage(__('Your transaction failed or has been cancelled!'));
            $this->_redirect('checkout/cart');
		}		
		
	}
	
    protected function _registerPaymentCapture($transactionId, $amount, $message)
    {
        $payment = $this->_order->getPayment();
		
		
        $payment->setTransactionId($transactionId)       
                ->setPreparedMessage($this->_createVivaPaymentsComment($message))
                ->setShouldCloseParentTransaction(false)
                ->setIsTransactionClosed(0)
                ->registerCaptureNotification(
                    $amount,
                    true 
                );

        $this->_order->save();

        $invoice = $payment->getCreatedInvoice();
        if ($invoice && !$this->_order->getEmailSent()) {
            $this->_orderSender->send($this->_order);
            $this->_order->addStatusHistoryComment(
                __('You notified customer about invoice #%1.', $invoice->getIncrementId())
            )->setIsCustomerNotified(
                true
            )->save();
        }
    }

    protected function _loadOrder($order_id)
    {
        $this->_order = $this->_orderFactory->create()->loadByIncrementId($order_id);

        if (!$this->_order && $this->_order->getId()) {
            throw new Exception('Could not find Magento order with id $order_id');
        }
    }

    protected function _success()
    {
        $this->getResponse()
             ->setStatusHeader(200);
    }

    protected function _failure()
    {
        $this->getResponse()
             ->setStatusHeader(400);
    }

    protected function _createVivaPaymentsComment($message = '')
    {       
        if ($message != '')
        {
            $message = $this->_order->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }
        return $message;
    }
	
	//mag23
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

	//mag23
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }	
}
