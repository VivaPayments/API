<?php 
header('content-type: text/html;charset=utf8');

// The POST URL and parameters
$request =  'https://demo.vivapayments.com/api/transactions/';	// demo environment URL
//$request =  'https://www.vivapayments.com/api/transactions';	// production environment URL

// Your merchant ID and API Key can be found in the 'Security' settings on your profile.
$merchant_id = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$api_key = 'xxxxxxxxxxxxx';

// Set your order code here
$order_code = xxxxxxxxxxxxxxxx; // int

$getargs = '?ordercode='.urlencode($order_code);

// Get the curl session object
$session = curl_init($request);

// Set the GET options.
curl_setopt($session, CURLOPT_HTTPGET, true);
curl_setopt($session, CURLOPT_URL, $request . $getargs);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_USERPWD, $merchant_id.':'.$api_key);
curl_setopt($session, CURLOPT_SSL_CIPHER_LIST, 'TLSv1.2');

// Do the GET and then close the session
$response = curl_exec($session);

curl_close($session);

// Parse the JSON response
try {
	// echo $response . '<br /><br />'; // you can see all properties with their values in a json string here.
		
	if(is_object(json_decode($response))){
	  	$resultObj=json_decode($response);
	}
} catch( Exception $e ) {
	echo $e->getMessage();
}
if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
	if(sizeof($resultObj->Transactions) > 0) {
		foreach ($resultObj->Transactions as $t){ // an order might have more than one transactions, or no transactions yet.
			echo 'TransactionId: ' . $t->TransactionId . '<br />';
			echo 'Amount: ' . $t->Amount . '<br />';
			echo 'StatusId: ' . $t->StatusId . '<br />';
			// check https://developer.vivawallet.com/web-api-integration/transaction-parameters/ for status result ids
			// "F" = The transaction has been completed successfully
			// "E" = The transaction was not completed because of an error
			echo 'CustomerTrns: ' . $t->CustomerTrns . '<br />';
		}
	} else {
		echo 'No transactions found. Make sure the order code exists and is created by your account.';
	}
} else {
	echo 'The following error occured: <strong>' . $resultObj->ErrorCode . '</strong>, ' . $resultObj->ErrorText;
}

?>
