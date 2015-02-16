<?php
require_once("../lib/NativeCheckoutClass.php");

if (count($_POST)>0){

	// Remember to change the API Credentials in the NativeCheckoutClass file
	$page=new NativeCheckout();
	$amount=100; // in cents
	$transactionId=$page->MakePayment($amount,$_POST[hidToken],$_POST[drpInstallments]);

	if ($transactionId!='0')
		echo "Transaction Succesful with Id :".$transactionId;
	else
		echo "Transaction Failure";
	
}
?>

<html>
<head>
	<title>VivaPayments Native Checkout Sample</title>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src="http://demo.vivapayments.com/web/checkout/js"></script>
	
	<script type="text/javascript">

		$(document).ready(function (){
			   VivaPayments.cards.setup({
					publicKey: 'CyfFrQukW8HOMYx+YPtD7RUv6RAPENfHkVZQC/PVM9c=',
					baseURL: 'http://demo.vivapayments.com',
					cardTokenHandler: function (response) {
							if (!response.Error) {
								$('#hidToken').val(response.Token);
								$('#payment-form').submit();
								return false;
							}
							else{
								console.log(response);
								alert(response.Error);
								return false;
							}
					},
					installmentsHandler: function (response) {
							if (!response.Error) {
									if (response.MaxInstallments == 0)
										return;
									$('#drpInstallments').show();
									for(i=1; i<=response.MaxInstallments; i++){
										$('#drpInstallments').append($("<option>").val(i).text(i));
									}
							}
							else
								alert(response.Error);
					}
			   });
		});
	</script>
</head>
<body>
	<form action="index.php" method="POST" id="payment-form">
		<div class="form-row">
		<label>
			<span>Cardholder Name</span>
			<input type="text" size="20" name=”txtCardHolder” autocomplete="off" data-vp="cardholder"/>
		</label>
		</div>
		<div class="form-row">
		<label>
			<span>Card Number</span>
			<input type="text" size="20" name=”txtCardNumber” autocomplete="off" data-vp="cardnumber"/>
		</label>
		</div>
		<div class="form-row">
		<label>
			<span>CVV</span>
			<input type="text" name=”txtCVV” size="4" autocomplete="off" data-vp="cvv"/>
		</label>
		</div>
		<div class="form-row">
		<label>
			<span>Expiration (MM/YYYY)</span>
			<input type="text" size="2" name=”txtMonth” autocomplete="off" data-vp="month"/>
		</label>
		<span> / </span>
		<input type="text" size="4" name=”txtYear” autocomplete="off" data-vp="year"/>
		</div>
		<div class="form-row">
			<label>
				<span>Installments</span>
				<select id="drpInstallments" name="drpInstallments" style="display:none"></select>
			</label>
		</div>

		<input type="hidden" name="OrderId" value="146228" /> <!--Custom Field-->
		<input type="hidden" name="hidToken" id="hidToken" /> <!--Hidden Field to hold the Generated Token-->
		<input type="button"  value="Submit" onclick="return VivaPayments.cards.requestToken();" />
	</form>
</body>
</html>