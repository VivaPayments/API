<h1>The transaction was completed successfully</h1>
<br />
<?php 
$OrderCode = $_GET["s"];
$TransactionId = $_GET["t"];

// The  URL and parameters
$request =  'http://demo.vivapayments.com/api/transactions/';	// demo environment URL
//$request =  'https://www.vivapayments.com/api/transactions/';	// production environment URL

// the merchant id is found in the self-care environments (developers menu)
$MerchantId = 'b301a18f-03fe-445c-b52c-26080a04c439';
// the password is set in the self-care environments (developers menu)
$Password = 'a123456';	

// Get the curl session object
$session = curl_init();

// Set query data here with the URL
curl_setopt($session, CURLOPT_POST, false);
curl_setopt($session, CURLOPT_URL, $request . $TransactionId);
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
	echo('transaction id: '. $resultObj->Transactions[0]->TransactionId . '<br />');
	echo('status id: '. $resultObj->Transactions[0]->StatusId . '<br />');
	echo('Amount: '. $resultObj->Transactions[0]->Amount . '<br />');
	echo('Email: '. $resultObj->Transactions[0]->Payment->Email . '<br />');
	echo('MerchantTRNS: '. $resultObj->Transactions[0]->MerchantTrns . '<br /><br />');
	
	// if the transaction can support recurring transctions 
	if($resultObj->Transactions[0]->Payment->RecurringSupport == 'true'){
		?>
			<a href="<?php echo('doTransaction.php?TransId='.$resultObj->Transactions[0]->TransactionId . '&Amount=120') ?>" >do transaction 1.20 euro</a><br /><br />
		<?php
	}
}
else{
	throw new Exception("Unable to find order code (" . $resultObj->ErrorText . ")");
}
?>

<a href="<?php echo('refund.php?TransId='.$resultObj->Transactions[0]->TransactionId . '&Amount=' . (floatval($resultObj->Transactions[0]->Amount)) * 100) ?>" >refund transaction</a>