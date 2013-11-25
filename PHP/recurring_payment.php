<?php 

// The POST URL and parameters
$request =  'http://demo.vivapayments.com/api/transactions/';	// demo environment URL
//$request =  'https://www.vivapayments.com/api/transactions';	// production environment URL

// Your merchant ID and API Key can be found in the 'Security' settungs on your profile.
$MerchantId = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$APIKey = 'xxxxxxxxxxxxx';	

//Set the ID of the Initial Transaction
$request .= 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'; 

//Set the Payment Amount
$Amount = 100;	// Amount in cents

$postargs = 'Amount='.$Amount;

// Get the curl session object
$session = curl_init($request);

// Set query data here with the URL
curl_setopt($session, CURLOPT_POST, true);
curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($session, CURLOPT_USERPWD, $MerchantId.':'.$APIKey);
curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($session);
curl_close($session);

// Parse the JSON response
try {
	$resultObj=json_decode($response);
} catch( Exception $e ) {
	echo $e->getMessage();
	
}

if ($resultObj->ErrorCode==0){
	// print JSON output
echo json_encode($resultObj);
}
else{
	echo $resultObj->ErrorText;
}

?>