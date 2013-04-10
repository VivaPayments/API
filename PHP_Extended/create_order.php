<?php 
// Redirect URL
$vivaPaymentFormRedirect = 'http://demo.vivapayments.com/web/newtransaction.aspx?ref=';	// demo environment URL
//$vivaPaymentFormRedirect = 'https://www.vivapayments.com/web/newtransaction.aspx?ref=';	// production environment URL

// The POST URL and parameters
$request =  'http://demo.vivapayments.com/api/orders';	// demo environment URL
//$request =  'https://www.vivapayments.com/api/orders';	// production environment URL

// the merchant id is found in the self-care environments (developers menu)
$MerchantId = 'b301a18f-03fe-445c-b52c-26080a04c439';
// the password is set in the self-care environments (developers menu)
$Password = 'a123456';

$Amount = 100;	// Amount in cents
$AllowRecurring = "true";	// used for recuring payments (doTransaction)
$SourceCode = "1920";	// the source was created in the self care environment see source.docx

$postargs = 'Amount='.urlencode($Amount).'&AllowRecurring='.$AllowRecurring.'&SourceCode='.$SourceCode;	

// Get the curl session object
$session = curl_init($request);

// Set the POST options.
curl_setopt($session, CURLOPT_POST, true);
curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($session, CURLOPT_USERPWD, $MerchantId.':'.$Password);

// Do the POST and then close the session
$response = curl_exec($session);
curl_close($session);

// Parse the JSON response
try {
	$resultObj=json_decode($response);
} catch( Exception $e ) {
	throw new Exception("Result is not a json object (" . $e->getMessage() . ")");
}

if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
	$orderId = $resultObj->OrderCode;
	//Redirect to payment form with the order code
	header ("Location: " . $vivaPaymentFormRedirect . $orderId);
}
else{
	throw new Exception("Unable to create order code (" . $resultObj->ErrorText . ")");
}

?>
