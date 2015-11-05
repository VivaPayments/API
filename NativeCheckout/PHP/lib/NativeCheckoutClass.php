<?php

class OrderRequest {}
class PaymentRequest {}

class NativeCheckout {

	private $merchantId = 'your_merchant_id';
	private $apiKey = 'your_api_key'; 
	private $baseApiUrl = "http://demo.vivapayments.com";
    private $paymentsUrl = "/api/transactions";
    private $paymentsCreateOrderUrl = "/api/orders";
    // A Source for which its Integration method is 
    // set to "Native/Pay with Viva Wallet" option.
    // See http://demo.vivapayments.com/selfcare/en-US/sources/paymentsources
	private $nativeCheckoutSourceCode = "Native";
	private $resultObj = "";
	
	public function MakePayment($amount,$cardToken,$installments){
		
		$orderCode=$this->CreateOrder($amount,$installments);
		
		$obj=new PaymentRequest();
		
		$obj->Amount=$amount;
		$obj->OrderCode=$orderCode;
		$obj->SourceCode=$this->nativeCheckoutSourceCode;
		$obj->CreditCard["Token"]=$cardToken;
		$obj->Installments=$installments;
		
		$resultObj = $this->ExecuteCall($this->baseApiUrl.$this->paymentsUrl,$obj);
				
		if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
			return $resultObj->TransactionId;
		}
		else{
			echo 'The following error occured: ' . $resultObj->ErrorText;
			return '0';
		}	
	}
	
	private function CreateOrder($amount,$installments){
	
		$obj=new OrderRequest();
		
		$obj->Amount=$amount;
		$obj->SourceCode=$this->nativeCheckoutSourceCode;
		$obj->MaxInstallments=$installments;
				
		$resultObj = $this->ExecuteCall($this->baseApiUrl.$this->paymentsCreateOrderUrl,$obj);
		
		if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
			return $resultObj->OrderCode;
		}
		else{
			echo 'The following error occured: ' . $resultObj->ErrorText;
			return 0;
		}	
	}	
	
	private function ExecuteCall($postUrl,$postobject){
	
		$postargs=json_encode($postobject);
	
		// Get the curl session object
		$session = curl_init($postUrl);
		
		// Set the POST options.
		curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($session, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json',                                                                                
			'Content-Length: ' . strlen($postargs))                                                                       
		);   
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_USERPWD, $this->merchantId.':'.$this->apiKey);
		curl_setopt($session, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
		
		curl_setopt($session, CURLOPT_HEADER, true);
		
		// Do the POST and then close the session
		$response = curl_exec($session);
		
		// Separate Header from Body
		$header_len = curl_getinfo($session, CURLINFO_HEADER_SIZE);
		$resHeader = substr($response, 0, $header_len);
		$resBody =  substr($response, $header_len);
		
		// Parse the JSON response
		try {
			if(is_object(json_decode($resBody))){
				$resultObj=json_decode($resBody);
			}else{
				preg_match('#^HTTP/1.(?:0|1) [\d]{3} (.*)$#m', $resHeader, $match);
				throw new Exception(trim($match[1]));
			}
		} catch( Exception $e ) {
			echo $e->getMessage();
		}
		
		curl_close($session);
		return $resultObj;
	}
	
}
?>
