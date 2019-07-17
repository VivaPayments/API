<?php 

// The POST URL and parameters
$request =  'https://demo.vivapayments.com/api/orders';	// demo environment URL
//$request =  'https://www.vivapayments.com/api/orders';	// production environment URL

// Your merchant ID and API Key can be found in the 'Security' settings on your profile.
$MerchantId = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$APIKey = 'xxxxxxxxxxxxx'; 	

//Set the Payment Amount
$Amount = 100;	// Amount in cents

//Set some optional parameters (Full list available here: https://developer.vivawallet.com/api-reference-guide/payment-api-details/#OP)
$AllowRecurring = 'true'; // This flag will prompt the customer to accept recurring payments in tbe future.
$RequestLang = 'en-US'; //This will display the payment page in English (default language is Greek)
$Source = 'Default'; // This will assign the transaction to the Source with Code = "Default". If left empty, the default source will be used.

$postargs = 'Amount='.urlencode($Amount).'&AllowRecurring='.$AllowRecurring.'&RequestLang='.$RequestLang.'&SourceCode='.$Source;

// Get the curl session object
$session = curl_init($request);


// Set the POST options.
curl_setopt($session, CURLOPT_POST, true);
curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_USERPWD, $MerchantId.':'.$APIKey);
curl_setopt($session, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

// Do the POST and then close the session
$response = curl_exec($session);

// Separate Header from Body
$header_len = curl_getinfo($session, CURLINFO_HEADER_SIZE);
$resHeader = substr($response, 0, $header_len);
$resBody =  substr($response, $header_len);

curl_close($session);

// Parse the JSON response
try {
	if(is_object(json_decode($resBody))){
	  	$resultObj=json_decode($resBody);
	}else{
		preg_match('#^HTTP/1.(?:0|1) [\d]{3} (.*)$#m', $resHeader, $match);
				throw new Exception("API Call failed! The error was: ".trim($match[1]));
	}
} catch( Exception $e ) {
	echo $e->getMessage();
}

if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
	$orderId = $resultObj->OrderCode;
	echo 'Your Order Code is: <b>'. $orderId.'</b>';
	echo '<br/><br/>';
	echo 'To simulate a successful payment, use the credit card 4111 1111 1111 1111, with a valid expiration date and 111 as CVV2.';
	echo '</br/><a href="http://demo.vivapayments.com/web/newtransaction.aspx?ref='.$orderId.'" >Make Payment</a>';
}
	
else{
	echo 'The following error occured: ' . $resultObj->ErrorText;
}

?>
