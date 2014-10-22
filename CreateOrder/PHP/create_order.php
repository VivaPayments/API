<?php 

// The POST URL and parameters
$request =  'http://demo.vivapayments.com/api/orders';	// demo environment URL
//$request =  'https://www.vivapayments.com/api/orders';	// production environment URL

// Your merchant ID and API Key can be found in the 'Security' settings on your profile.
$MerchantId = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$APIKey = 'xxxxxxxxxxxxx'; 	

//Set the Payment Amount
$Amount = 100;	// Amount in cents

//Set some optional parameters (Full list available here: https://github.com/VivaPayments/API/wiki/Optional-Parameters)
$AllowRecurring = 'true'; // This flag will prompt the customer to accept recurring payments in tbe future.
$RequestLang = 'en-US'; //This will display the payment page in English (default language is Greek)


$postargs = 'Amount='.urlencode($Amount).'&AllowRecurring='.$AllowRecurring.'&RequestLang='.$RequestLang;

// Get the curl session object
$session = curl_init($request);


// Set the POST options.
curl_setopt($session, CURLOPT_POST, true);
curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_USERPWD, $MerchantId.':'.$APIKey);
curl_setopt($session, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

// Do the POST and then close the session
$response = curl_exec($session);
curl_close($session);

// Parse the JSON response
try {
	if(is_object(json_decode($response))){
	  	$resultObj=json_decode($response);
	}else{
		throw new Exception("Result is not a json object: ");
	}
} catch( Exception $e ) {
	echo $e->getMessage();
}

if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
	$orderId = $resultObj->OrderCode;
	echo 'Your Order Code is: <b>'. $orderId.'</b>';
	echo '<br/><br/>';
	echo 'To simulate a successfull payment, use the credit card 4111 1111 1111 1111, with a valid expiration date and 111 as CVV2.';
	echo '</br/><a href="http://demo.vivapayments.com/web/newtransaction.aspx?ref='.$orderId.'" >Make Payment</a>';
}
	
else{
	echo 'The following error occured: ' . $resultObj->ErrorText;
}

?>
