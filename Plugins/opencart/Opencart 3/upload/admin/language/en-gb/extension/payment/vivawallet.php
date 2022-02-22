<?php
// Heading
$_['heading_title']      = 'Viva Wallet Smart Checkout';

// Text
$_['text_payment']        = 'Payment';
$_['text_success']        = 'Viva Wallet Smart Checkout settings modified successfully';
$_['text_webcheckout']	  = 'WebCheckout';
$_['text_receipt']	 	  = 'Electronic Receipt';
$_['text_checkoutreceipt']= 'WebCheckout and Receipt';

$_['text_instalments'] 	 = 'Example: 150:3,600:6 - Order total 150 -> allow 3 instalments, order total 600 -> allow 3 and 6 instalments - Leave empty to deactivate instalments';

$_['text_vivawallet']     = '<a onclick="window.open(\'https://www.vivapayments.com\');"><img src="view/image/payment/vivawallet.png" alt="Vivawallet" title="Vivawallet" /></a>';

// Entry
$_['entry_total']               = 'Total:';
$_['help_total']                = 'The checkout total the order must reach before this payment method becomes active.';
$_['entry_merchantid']			= 'Merchant ID:';
$_['entry_merchantpass']		= 'API Key:';
$_['entry_serviceid']			= 'ServiceID:';
$_['entry_source']				= 'Source Code:';
$_['entry_maxinstal']			= 'Instalments:';

$_['entry_orderurl']			= 'OrderCode URL:';
$_['entry_url']					= 'Redirect URL:';
$_['entry_processed_status']	= 'Processed Status:';
$_['entry_failed_status']		= 'Failed Status:';
$_['entry_geo_zone']			= 'Geo Zone:';
$_['entry_status']				= 'Status:';
$_['entry_sort_order']			= 'Sort Order:';
$_['entry_mode']				= 'Transaction mode:';

// Error
$_['error_permission']    = 'Warning: You do not have permission to modify payment Viva Wallet Smart Checkout!';
$_['error_merchantid']    = 'Merchant ID Required';
$_['error_merchantpass']  = 'Transaction Password Required';
$_['error_orderurl']      = 'Order Url Required';
$_['error_url']           = 'Redirect URL Required';