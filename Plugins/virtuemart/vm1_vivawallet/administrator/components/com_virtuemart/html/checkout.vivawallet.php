<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

mm_showMyFileName( __FILE__ );

if( !isset( $_GET['s'] ) || empty( $_GET['s'] )) {
  echo $VM_LANG->_('VM_CHECKOUT_ORDERIDNOTSET');
}
else {
isset($_GET['s']) ? $ordercode = $_GET['s'] : $ordercode = '';
isset($_GET['stat']) ? $status = $_GET['stat'] : $status = '';

include_once(CLASSPATH ."payment/ps_vivawallet.cfg.php");
$merchant_password = VIVAWALLET_PASS;
		
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
      } elseif($status=='webhook'){

		$MerchantID =  VIVAWALLET_ID;
		$Password 	=  html_entity_decode(VIVAWALLET_PASS);
			
		$curl_adr 	= 'https://www.vivapayments.com/api/messages/config/token/';
		
		$curl = curl_init();
		if (preg_match("/https/i", $curl_adr)) {
		curl_setopt($curl, CURLOPT_PORT, 443);
		}
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_URL, $posturl);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
		$curlversion = curl_version();
		if(!preg_match("/NSS/" , $curlversion['ssl_version'])){
		curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, "TLSv1");
		}
		$response = curl_exec($curl);
		
		if(curl_error($curl)){
		if (preg_match("/https/i", $curl_adr)) {
		curl_setopt($curl, CURLOPT_PORT, 443);
		}
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		}
		
		curl_close($curl);
		echo $response;
		
		try {
			
		if(is_object(json_decode($postdata))){
			$resultObj=json_decode($postdata);
		}
		} catch( Exception $e ) {
			echo $e->getMessage();
		}

		if(sizeof($resultObj->EventData) > 0) {
		$StatusId = $resultObj->EventData->StatusId;
		$OrderCode = $resultObj->EventData->OrderCode;
		$statustr = $this->vivawallet_processing;
		
			if($StatusId=='F'){
			// UPDATE THE ORDER STATUS to 'PAID'
            $d['order_status'] = "C";
            require_once ( CLASSPATH . 'ps_order.php' );
            $ps_order= new ps_order;
            $ps_order->order_status_update($d);
			}
			?>

    
    <?php
		}
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