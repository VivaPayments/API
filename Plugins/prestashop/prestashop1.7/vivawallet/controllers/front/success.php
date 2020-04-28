<?php
class VivawalletSuccessModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
	
	  if(isset($_GET['s']) && $_GET['s']!=''){
	  $errors = '';
	  $OrderCode = addslashes($_GET['s']);
	  
	  $check_query = "select * from vivawallet_data where OrderCode='".$OrderCode."' ORDER BY id DESC";
	  $check = Db::getInstance()->executeS($check_query, $array = true, $use_cache = 0);
	  $oid = $check[0]['ref'];
	  
	  $cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active){
			
			$id_cart = (int)$oid;
			$this->context->cart = new Cart($id_cart);
			$this->context->cookie->id_cart = $id_cart;
			
			$cart = new Cart((int) $id_cart);
            if (Validate::isLoadedObject($cart)) {
                $customer = new Customer((int) $cart->id_customer);
                if (Validate::isLoadedObject($customer)) {
                    $customer->logged = 1;
                    $this->context->customer = $customer;
                    $this->context->cookie->id_customer = (int) $customer->id;
                    $this->context->cookie->customer_lastname = $customer->lastname;
                    $this->context->cookie->customer_firstname = $customer->firstname;
                    $this->context->cookie->logged = 1;
                    $this->context->cookie->check_cgv = 1;
                    $this->context->cookie->is_guest = $customer->isGuest();
                    $this->context->cookie->passwd = $customer->passwd;
                    $this->context->cookie->email = $customer->email;
                }
            }
			
			if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active){
			 Tools::redirect('index.php?controller=order&step=1');
			}
			
		}	
			
		$customer = new Customer((int) $cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');
		   
	  $currency = $this->context->currency;
	  $total = (float)$cart->getOrderTotal(true, Cart::BOTH);  
	  
	  $MerchantID =  trim(Configuration::get('VIVAWALLET_MERCHANTID'));
	  $APIKey =   html_entity_decode(Configuration::get('VIVAWALLET_MERCHANTPASS'));
	  $BaseUrl =  trim(Configuration::get('VIVAWALLET_URL'));
	  $request = $BaseUrl."/api/transactions/";
		
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
                    $message = $this->l('Transactions completed Successfully');
				}
			} else {
				$message = $this->l('No transactions found. Make sure the order code exists and is created by your account.');
			}
		} else {
			$message = $this->l('The following error occured:') . '<strong>' . $resultObj->ErrorCode . '</strong>, ' . $resultObj->ErrorText;
		}
        
		if(isset($StatusId) && strtoupper($StatusId) == 'F')
		{
		  $transtat_query = "select * from vivawallet_data where OrderCode='".$OrderCode."' ORDER BY id DESC";
		  $transtat = Db::getInstance()->executeS($transtat_query, $array = true, $use_cache = 0);
		  $order_state = $transtat[0]['order_state'];
		
		  $details = array(
						'id_transaction' => $TransactionId,
						'transaction_id' => $TransactionId
					);
		
		   if($order_state=='I'){
		    $this->context->shop = new Shop((int) $this->context->cart->id_shop);
			$this->module->validateOrder($cart->id, _PS_OS_PAYMENT_, $total, $this->module->displayName, 'OrderCode: '.$OrderCode, $details,(int)$currency->id,false,$customer->secure_key,$this->context->shop);
		   }
		   
		   $update_query = "update vivawallet_data set order_state='P' where OrderCode='".$OrderCode."'";
		   $update = Db::getInstance()->execute($update_query);
		  
		   Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
		
		} else  {
		
		   $update_query = "update vivawallet_data set order_state='F' where OrderCode='".$OrderCode."'";
		   $update = Db::getInstance()->execute($update_query);
		
		   $this->errors[] = $this->l('Transaction Failed.') . '<br>' . $this->l('Order Code: ') . $OrderCode;
		   $this->redirectWithNotifications('index.php?controller=order&step=1');
		
		}
	  
	  
	
	} else {
	echo 'No valid input received.';
	}

	}
}