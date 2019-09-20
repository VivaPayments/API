<?php

class VivawalletWebhookModuleFrontController extends ModuleFrontController
{
	 
	public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
	 return false;
	}
	 
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
	
		$postdata = file_get_contents("php://input");
	
		$MerchantID =  trim(Configuration::get('VIVAWALLET_MERCHANTID'));
		$Password =   html_entity_decode(Configuration::get('VIVAWALLET_MERCHANTPASS'));
		$BaseUrl =  trim(Configuration::get('VIVAWALLET_URL'));
		$curl_adr 	= $BaseUrl.'/api/messages/config/token/';
	
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_URL, $curl_adr);
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
		echo $response;
		
		try {
			
		if(is_object(json_decode($postdata))){
			$resultObj=json_decode($postdata);
		}
		} catch( Exception $e ) {
			echo $e->getMessage();
		}
	
	
		if(isset($resultObj->EventData->StatusId) && $resultObj->EventData->StatusId=='F') {
		$StatusId = $resultObj->EventData->StatusId;
		$OrderCode = $resultObj->EventData->OrderCode;
		$TransactionId = $resultObj->EventData->TransactionId;
		$statustr = $this->vivawallet_processing;
		
		$cart = $this->context->cart;
		$customer = new Customer($cart->id_customer);
		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
	
		  $transtat_query = "select * from vivawallet_data where OrderCode='".$OrderCode."' ORDER BY id DESC";
		  $transtat = Db::getInstance()->executeS($transtat_query, $array = true, $use_cache = 0);
		  
		  if($transtat[0]['order_state']=='I' && $StatusId=='F'){
		  $update_query = "update vivawallet_data set order_state='P' where OrderCode='".$OrderCode."'";
		  $update = Db::getInstance()->execute($update_query);
		
		  $details = array(
						'id_transaction' => $TransactionId,
						'transaction_id' => $TransactionId
					);
		
		   $this->module->validateOrder($cart->id, _PS_OS_PAYMENT_, $total, $this->module->displayName, 'OrderCode: '.$OrderCode, $details,(int)$currency->id,false,$customer->secure_key);
		
		} 
		}

	}
}
