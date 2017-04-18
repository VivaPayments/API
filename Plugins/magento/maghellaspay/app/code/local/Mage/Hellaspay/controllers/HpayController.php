<?php

class Mage_Hellaspay_HpayController extends Mage_Core_Controller_Front_Action
{
    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }
	
	public function getHellaspay()
    {
        return Mage::getSingleton('hellaspay/hpay');
    }
	
	public function setHellaspayResponse($response)
    {
    	if (count($response)) {
            $this->_hellaspayResponse = $response;
        } else {
			$this->_hellaspayResponse = null;
		}
        return $this;
    }

    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setHpayQuoteId($session->getQuoteId());
		
		$this->getResponse()->setBody($this->getLayout()->createBlock('hellaspay/hpay_redirect')->toHtml());
        $session->unsQuoteId();
		$session->unsRedirectUrl(); //unset 06/2013
    }

	public function HpokAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setHpayQuoteId($session->getQuoteId());
		
		if(preg_match("/s=/i", $_SERVER['REQUEST_URI'])) {
		  $strip_ref = explode("s=",$_SERVER['REQUEST_URI']);
		  //$tm_ref = $strip_ref[1];
		   $tm_ref = addslashes($_GET['s']);
		}  
		
		$tm_orderid = $session->getLastRealOrderId();

		if (isset($tm_ref) && isset($tm_orderid)) {
		$this->setHellaspayResponse($this->getRequest()->getPost());

			$order = Mage::getModel('sales/order');
		    $order->loadByIncrementId($tm_orderid);
			
		if(isset($tm_ref) && $tm_ref!=''){
		
		//register transactions
		$charge = number_format($order->getBaseGrandTotal(), 2, '.', '');
		$payment = $order->getPayment();
		$payment->setTransactionId($tm_ref)
		->setParentTransactionId(null)
		 ->setIsTransactionClosed(1)
		->registerCaptureNotification($charge);
		
		$orderComment = 'Viva OrderCode: ' . $tm_ref . '<br />';
		
		$newstatus='';
		$newstatus=$this->getHellaspay()->getOrderStatus();
		
		if(!isset($newstatus) || $newstatus == ''){
		$newstatus = 'pending';
		}
		
		if($newstatus =='complete'){
		$order->setData('state', "complete");
		$order->setStatus("complete");
		$history = $order->addStatusHistoryComment($orderComment, false);
		$history->setIsCustomerNotified(true);
		//} elseif($this->getHellaspay()->getConfigData('order_status') !='pending') {
		} else {
		$newstate = $newstatus;
		$order->setData('state', $newstate);
		$order->setStatus($newstate);
		$history = $order->addStatusHistoryComment($orderComment, false);
		$history->setIsCustomerNotified(true);
		}
		
		//invoice_pending
		if ($order->canInvoice()) {
                $order->getPayment()->setSkipTransactionCreation(false);
                $invoice = $order->prepareInvoice();
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                $invoice->register();
                Mage::getModel('core/resource_transaction')
                   ->addObject($invoice)
                   ->addObject($order)
                   ->save();
        }
		
		if($order->hasInvoices()){
		$paid = (string)Mage_Sales_Model_Order_Invoice::STATE_PAID;
		
		foreach ($order->getInvoiceCollection() as $orderInvoice) {
		$orderInvoice->setState($paid)
				->setTransactionId($tm_ref)
				->save();
		}
		}
		
        $order->save();
		$order->sendNewOrderEmail()->setEmailSent(true)->save();
        $session->unsQuoteId();		
		$this->_redirect('checkout/onepage/success', array('_secure'=>true)); 
		} else {
		$orderComment = 'Transaction failed';
		$order->cancel()->save();
        $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED)
                    ->addStatusHistoryComment($orderComment)
                    ->save();
					
		$session->unsQuoteId();	
		$this->_redirect('checkout/onepage/failure');
		}
		} else { 
		$this->_redirect('checkout/onepage/failure'); }
	}



	public function HpnokAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setHpayQuoteId($session->getQuoteId());
		
		if(preg_match("/s=/i", $_SERVER['REQUEST_URI'])) {
		  $strip_ref = explode("s=",$_SERVER['REQUEST_URI']);
		  //$tm_ref = $strip_ref[1];
		   $tm_ref = addslashes($_GET['s']);
		}  
		
		$tm_orderid = $session->getLastRealOrderId();		
		
		if (isset($tm_ref) && isset($tm_orderid)) {
		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($tm_orderid);
		
		$orderComment = 'Transaction failed';
		$order->cancel()->save();
        $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED)
                    ->addStatusHistoryComment($orderComment)
                    ->save();
					
        $session->unsQuoteId();
        $this->_redirect('checkout/onepage/failure');
		} else { 
			$session->unsQuoteId();
        	$this->_redirect('checkout/onepage/failure'); }
		
    }
	
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getHpayQuoteId(true));
        $this->_redirect('checkout/cart');
     }

 
    public function  successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getHpayQuoteId(true));

        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }   

    public function  failAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getHpayQuoteId(true));
		
		$this->_redirect('checkout/onepage/failure');
	}

}
