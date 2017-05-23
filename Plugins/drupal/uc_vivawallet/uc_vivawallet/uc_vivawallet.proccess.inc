<?php
function uc_vivawallet_error() {
  $ref = addslashes($_GET['s']);
  $errormsg = "Transaction failed or cancelled";
  if (isset($ref) && $ref !=''){
  db_query("UPDATE {uc_vivawallet} set status = '$errormsg' WHERE OrderCode = '".$ref."'");
  }	  
  drupal_set_message(t('An error has occurred in your payment. Please review your cart and try again.'));
  drupal_goto('cart');
}

function uc_vivawallet_complete() {
  $ref = addslashes($_GET['s']);

  if (isset($ref) && $ref !=''){
  
  $dborder = db_result(db_query("SELECT order_id FROM {uc_vivawallet} WHERE OrderCode = '".$ref."'"));
  $dbamount = db_result(db_query("SELECT amount FROM {uc_vivawallet} WHERE OrderCode = '".$ref."'"));
  $dbcurrency = db_result(db_query("SELECT currency FROM {uc_vivawallet} WHERE OrderCode = '".$ref."'"));
  
  $order = uc_order_load($dborder);

  if ($order === FALSE ) {
    drupal_set_message(t('An error has occurred in your payment. Please review your cart and try again.'));
    drupal_goto('cart');
  }
  
  if (intval($_SESSION['cart_order']) != $order->order_id) {
    $_SESSION['cart_order'] = $order->order_id;
  }

	db_query("UPDATE {uc_vivawallet} set status = 'Confirmed' WHERE OrderCode = '".$ref."'");

	$order = uc_order_load($dborder);
	$realamount = $dbamount / 100;

    uc_cart_complete_sale($order, variable_get('uc_new_customer_login', FALSE));
    uc_payment_enter($order->order_id, 'vivawallet', $realamount, 0, NULL, t('Paid by Credit Card (Vivawallet)'));
    uc_order_comment_save($order->order_id, 0, 'Payment received - OrderCode: '.$ref, 'admin');

    uc_cart_empty(uc_cart_get_id());
    $_SESSION['uc_checkout'][$_SESSION['cart_order']]['do_complete'] = TRUE;
	drupal_set_message(t('Thank you for your order!'));
    drupal_goto('cart/checkout/complete');

  return ;
  }
}