<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/hellaspay.php');
$hellaspay = new hellaspay();


?>
<noscript>
<p><?php echo  $hellaspay->l('In case you are not redirected to the payment page within 10 seconds, click the "Pay Now" button below.'); ?></p>
</noscript>
<form id="payment_form" name="payment_form" action="<?php echo $_POST['HellaspayUrl']; ?>" method="get">

<?php
	foreach( $_POST as $name => $value ) {
	if($name !='HellaspayUrl'){
		echo '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
		}
	}
?>
<noscript>
<input type="submit" value="<?php echo $hellaspay->l('Pay Now'); ?>" />
</noscript>
</form>
<script type="text/javascript">
	<!--
	document.getElementById('payment_form').submit();
	//-->
</script>