<?php 
$TID = $_GET["TransId"];	// TransactionId
$Amount = $_GET["Amount"];		// refund amount

// The  URL and parameters
$request =  'http://demo.vivapayments.com/api/transactions';	// demo environment URL
//$request =  'https://www.vivapayments.com/api/transactions';	// production environment URL

// the merchant id is found in the self-care environments (developers menu)
$MerchantId = 'b301a18f-03fe-445c-b52c-26080a04c439';
// the password is set in the self-care environments (developers menu)
$Password = 'a123456';	

$args = '/'.$TID.'?Amount='.$Amount;

// Get the curl session object
$session = curl_init();

// Set query data here with the URL
curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'DELETE');
 
curl_setopt($session, CURLOPT_POST, false);
curl_setopt($session, CURLOPT_URL, $request . $args);
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_USERPWD, $MerchantId.':'.$Password);
curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($session);
curl_close($session);

// Parse the JSON response
try {
	$resultObj=json_decode($response);
} catch( Exception $e ) {
	throw new Exception("Result is not a json object (" . $e->getMessage() . ")");
}

if ($resultObj->ErrorCode==0){
	// print results
	echo('transaction id: '. $resultObj->TransactionId . '<br />');	// for details do transaction request with the transactionid
	echo('status id: '. $resultObj->StatusId . '<br />');	
	/*
	E - The transaction was not completed because of an error
	A - The transaction is in progress
	M - The cardholder has disputed the transaction with the issuing Bank
	U - A disputed transaction has been refunded
	X - The transaction was cancelled by the merchant
	R - The transaction has been fully or partially refunded
	F - The transaction has been completed successfully 
	*/
}
else{
	throw new Exception("Unable to find order code (" . $resultObj->ErrorText . ")");
}

?>