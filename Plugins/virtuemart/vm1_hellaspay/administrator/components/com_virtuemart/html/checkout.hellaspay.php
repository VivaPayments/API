<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

mm_showMyFileName( __FILE__ );

if( !isset( $_GET['s'] ) || empty( $_GET['s'] )) {
  echo $VM_LANG->_('VM_CHECKOUT_ORDERIDNOTSET');
}
else {
isset($_GET['s']) ? $ordercode = $_GET['s'] : $ordercode = '';
isset($_GET['stat']) ? $status = $_GET['stat'] : $status = '';

include_once(CLASSPATH ."payment/ps_hellaspay.cfg.php");
$merchant_password = HELLASPAY_PASS;
		
	//include( CLASSPATH. "payment/ps_paypal.cfg.php" );
	$order_id = intval($emp_orderid);
	
	$q = "SELECT order_id, order_payment_trans_id FROM #__{vm}_order_payment WHERE #__{vm}_order_payment.order_payment_trans_id='".$ordercode."'";
	$db->query($q);
	if ($db->next_record()) {
		$d['order_id'] = $db->f("order_id");
	
		if($status=='ok'){

			// UPDATE THE ORDER STATUS to 'PAID'
            $d['order_status'] = "C";
            require_once ( CLASSPATH . 'ps_order.php' );
            $ps_order= new ps_order;
            $ps_order->order_status_update($d);
			?>

	
			<img src="<?php echo VM_THEMEURL ?>images/button_ok.png" alt="Success" style="border: 0;" />
			<h2>Thanks for your payment.</h2>
			<p>The transaction was successful.</p>
    
    <?php
      }
		else {

            // the Payment wasn't successful. Maybe the Payment couldn't
            // be verified and is pending
            // UPDATE THE ORDER STATUS to 'CANCELLED'
            $d['order_status'] = "X";
            require_once ( CLASSPATH . 'ps_order.php' );
            $ps_order= new ps_order;
            $ps_order->order_status_update($d);

			?>
			<img src="<?php echo VM_THEMEURL ?>images/button_cancel.png" alt="<?php echo $VM_LANG->_('VM_CHECKOUT_FAILURE'); ?>" style="border: 0;" />
			<h2>Payment failed or was cancelled</h2>
			<p><?php echo $VM_LANG->_('PHPSHOP_PAYMENT_ERROR') ?></p>
    
    <?php
    } ?>
    <br />
     <p><a href="index.php?option=com_virtuemart&page=account.order_details&order_id=<?php echo $order_id ?>">
     <?php echo $VM_LANG->_PHPSHOP_ORDER_LINK ?></a>
     </p>
    <?php
	}
	else {
		echo "Order not found!";
	}
}
?>