<?php
function commerce_vivawallet_fail() {
if(preg_match("/s=/i", $_SERVER['REQUEST_URI'])) {

  $ref = addslashes($_GET['s']);
	  
  $errormsg = "Transaction failed or cancelled";

  db_query("UPDATE {commerce_vivawallet} set status = '$errormsg' WHERE OrderCode = :ref", array(':ref' => $ref));
  drupal_set_message(t('An error has occurred in your payment. Please review your cart and try again.'));

 $dborder = db_query("SELECT order_id FROM {commerce_vivawallet} WHERE OrderCode = :ref", array(':ref' => $ref))->fetchField();
 $order = commerce_order_load($dborder);
  
  drupal_set_message(t('Payment unsuccessful!'), 'error');
  $build = array();
  if ($message = commerce_vivawallet_get_settings('vivawallet_fail_message')) {
    $build['message_fail']['#markup'] = $message;
  }
  return $build;  
    
  }
}

function commerce_vivawallet_success() {
if(preg_match("/s=/i", $_SERVER['REQUEST_URI'])) {

  $ref = addslashes($_GET['s']);
  
  $dborder = db_query("SELECT order_id FROM {commerce_vivawallet} WHERE OrderCode = :ref", array(':ref' => $ref))->fetchField();
  $dbamount = db_query("SELECT amount FROM {commerce_vivawallet} WHERE OrderCode = :ref", array(':ref' => $ref))->fetchField();
  $realamount = $dbamount / 100;
  $dbcurrency = db_query("SELECT currency FROM {commerce_vivawallet} WHERE OrderCode = :ref", array(':ref' => $ref))->fetchField();
  
    $order_in = $dborder;
    $amount_in = $realamount;

      $order = commerce_order_load($order_in);
      $wrapper = entity_metadata_wrapper('commerce_order', $order);

      $currency_code = $wrapper->commerce_order_total->currency_code->value();
      $amount = $wrapper->commerce_order_total->amount->value();
      // Get real price.
      $amount = commerce_currency_amount_to_decimal($amount, $currency_code);

      $amount_received = abs($amount_in);
      $amount_stored = abs($amount);

      if ($amount_received == $amount_stored) {
	  db_query("UPDATE {commerce_vivawallet} set status = 'Confirmed' WHERE OrderCode = :ref", array(':ref' => $ref));
        commerce_vivawallet_create_transaction($order_in, commerce_vivawallet_get_settings('vivawallet_status'));

		$order = commerce_order_status_update($order, commerce_vivawallet_get_settings('vivawallet_status'), FALSE, NULL, t('Payment gateway confirmed transaction.'));
		
        watchdog('commerce_vivawallet', 'Order #@order paid successfully.', array('@order' => $order_in), WATCHDOG_INFO);
      }
      else {
	  db_query("UPDATE {commerce_vivawallet} set status = 'Failed' WHERE OrderCode = :ref", array(':ref' => $ref));
        watchdog('commerce_vivawallet', 'Order #@order was not paid: recieved (@am_rec) and real(@am_int) order info do not match.', array(
          '@order' => $order_in,
          '@am_rec' => $amount_in,
          '@am_int' => $amount,
          ), WATCHDOG_ERROR);
      }

	$message = t('Thank you, payment successful. Order #@shop_order,<br />', array('@shop_order' => $dborder));
    drupal_set_message($message, 'status');
    if ($message = commerce_vivawallet_get_settings('vivawallet_success_message')) {
      $build['message_success']['#markup'] = $message;
    }      
	return $build;  

  }
}