<?php 

// The POST URL and parameters
$request =  'https://demo.vivapayments.com/api/orders';	// demo environment URL
//$request =  'https://www.vivapayments.com/api/orders';	// production environment URL

// Your merchant ID and API Key can be found in the 'Security' settings on your profile.
$merchant_id = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$api_key = 'xxxxxxxxxxxxx';

//Set the Payment Amount
$amount = 100;	// Amount in cents

//Set some optional parameters (Full list available here: https://developer.vivawallet.com/api-reference-guide/payment-api/#tag/Payments/paths/~1orders/post)
$allow_recurring = 'true'; // If set to true, this flag will prompt the customer to accept recurring payments in the future.
$request_lang = 'en-US'; //This will display the payment page in English (default language is Greek)
$source = 'Default'; // This will assign the transaction to the Source with Code = "Default". Alternatively, you can use the 4-digit code of a custom payment source if set up.

$postargs = 'Amount='.urlencode($amount).'&AllowRecurring='.$allow_recurring.'&RequestLang='.$request_lang.'&SourceCode='.$source;

// Get the curl session object
$session = curl_init($request);


// Set the POST options.
curl_setopt($session, CURLOPT_POST, true);
curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_USERPWD, $merchant_id.':'.$api_key);
curl_setopt($session, CURLOPT_SSL_CIPHER_LIST, 'TLSv1.2');

// Do the POST and then close the session
$response = curl_exec($session);

// Separate Header from Body
$header_len = curl_getinfo($session, CURLINFO_HEADER_SIZE);
$res_header = substr($response, 0, $header_len);
$res_body =  substr($response, $header_len);

curl_close($session);

// Parse the JSON response
try {
	if(is_object(json_decode($res_body))){
	  	$result_obj=json_decode($res_body);
	}else{
		preg_match('#^HTTP/1.(?:0|1) [\d]{3} (.*)$#m', $res_header, $match);
				throw new Exception("API Call failed! The error was: ".trim($match[1]));
	}
} catch( Exception $e ) {
	echo $e->getMessage();
}

if ($result_obj->ErrorCode==0){	//success when ErrorCode = 0
	$orderId = $result_obj->OrderCode;
	echo 'Your Order Code is: <b>'. $orderId.'</b>';
	echo '<br/><br/>';
	echo 'To simulate a successful payment, use the 16-digit test credit card 5511070000000020, with a valid expiration date and 111 as CVV2.';
	echo '</br/><a href="https://demo.vivapayments.com/web/newtransaction.aspx?ref='.$orderId.'" >Make Payment</a>';
}
	
else{
	echo 'The following error occured: ' . $result_obj->ErrorText;
}

?>
