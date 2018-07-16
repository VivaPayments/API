<?php
/*
Plugin Name: WooCommerce Vivawallet Gateway
Plugin URI: http://www.vivawallet.com/
Description: Extends WooCommerce with the Vivawallet gateway.
Version: 3.3.5
Author: Viva Wallet
Author URI: http://www.vivawallet.com/
Text Domain: vivawallet-for-woocommerce
Domain Path: /languages
*/

/*  Copyright 2017  Vivawallet.com 
 *****************************************************************************
 * @category   Payment Gateway WP Woocommerce
 * @package    Vivawallet v3.3.5
 * @author     Viva Wallet
 * @copyright  Copyright (c)2017 Vivawallet http://www.vivawallet.com/
 * @License    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 ****************************************************************************** 
*/

/* Add a custom payment class to WC
  ------------------------------------------------------------ */
add_action('plugins_loaded', 'woocommerce_vivawallet', 0);
function woocommerce_vivawallet()
{
	if (!class_exists('WC_Payment_Gateway'))
		return; // if the WC payment gateway class is not available, do nothing
	if(class_exists('WC_VIVAWALLET'))
		return;
		
	
			
class WC_VIVAWALLET extends WC_Payment_Gateway
{
	public function __construct()
	{
		$plugin_dir = plugin_dir_url(__FILE__);
		
		global $woocommerce;

		$this->id = 'vivawallet';
		$this->icon = apply_filters('woocommerce_vivawallet_icon', ''.$plugin_dir.'vivawallet.png');
		$this->has_fields = false;
		
		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables
		$this->title = $this->settings['title'];
		$this->description = $this->settings['description'];
		$this->vivawallet_merchantid = $this->settings['vivawallet_merchantid'];
		$this->vivawallet_merchantpass = html_entity_decode($this->settings['vivawallet_merchantpass']);
		$this->vivawallet_source = $this->settings['vivawallet_source'];
		$this->vivawallet_instal = $this->settings['vivawallet_instal'];
		$this->vivawallet_testmode = $this->settings['testmode'];
		$this->vivawallet_processing = $this->settings['vivawallet_processing'];


		// Actions
		add_action('valid-vivawallet-standard-ipn-reques', array($this, 'successful_request') );
		add_action('woocommerce_receipt_vivawallet', array($this, 'receipt_page'));
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		
		// Payment listener/API hook
		add_action( 'woocommerce_api_wc_vivawallet', array( $this, 'check_ipn_response' ) );
		
		if ( !defined('PLUGINDIR') )
		define( 'PLUGINDIR', 'wp-content/plugins' );
		load_plugin_textdomain('vivawallet-for-woocommerce', PLUGINDIR.'/vivawallet-for-woocommerce/languages','vivawallet-for-woocommerce/languages');

		if (!$this->is_valid_for_use())
		{
			$this->enabled = false;
		}
	}
	
	/**
	 * Check if this gateway is enabled and available in the user's country
	 */
	function is_valid_for_use()
	{
		if (!in_array( get_woocommerce_currency(), array('GBP', 'BGN', 'RON', 'EUR'))) return false;
		
		return true;
	}
	
	/**
	* Admin Panel Options 
	* - Options for bits like 'title' and availability on a country-by-country basis
	**/
	public function admin_options() {
		global $wpdb;
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}vivawallet_data (id int(11) unsigned NOT NULL AUTO_INCREMENT, ref varchar(100) DEFAULT NULL, ordercode varchar(255) DEFAULT NULL, email varchar(150) DEFAULT NULL, orderid varchar(100) DEFAULT NULL, total_cost int(11) DEFAULT NULL, currency char(3) DEFAULT NULL, tm_password varchar(100) DEFAULT NULL, order_state char(1) DEFAULT NULL, sessionid varchar(32) DEFAULT NULL, timestamp datetime DEFAULT NULL, PRIMARY KEY (id))";
		$wpdb->query($query);
				
		?>
		<h3><?php _e('Vivawallet', 'vivawallet-for-woocommerce'); ?></h3>
		<p><?php _e('Vivawallet redirects customers to their secure server for making payments.', 'vivawallet-for-woocommerce'); ?></p>
		<table class="form-table">
		<?php
			if ( $this->is_valid_for_use() ) :
    	
    			// Generate the HTML For the settings form.
    			$this->generate_settings_html();
    		
    		else :
		?>
		<div class="inline error"><p><strong><?php _e('Gateway Disabled', 'vivawallet-for-woocommerce'); ?></strong>: <?php _e('Vivawallet does not support your store currency.', 'vivawallet-for-woocommerce' ); ?></p></div>
		<?php
			endif;
		?>
		</table><!--/.form-table-->
		<?php
    } // End admin_options()

	function init_form_fields()
	{
		$this->form_fields = array
			(
				'enabled' => array
				(
					'title' => __('Enable/Disable', 'vivawallet-for-woocommerce'),
					'type' => 'checkbox',
					'label' => __('Enable Vivawallet', 'vivawallet-for-woocommerce'),
					'default' => 'yes'
				),
				'title' => array
				(
					'title' => __('Title', 'vivawallet-for-woocommerce'),
					'type' => 'text', 
					'description' => __( 'This controls the title which the user sees during checkout.', 'vivawallet-for-woocommerce' ), 
					'default' => __('Vivawallet', 'vivawallet-for-woocommerce')
				),
				'description' => array(
					'title' => __( 'Description', 'woocommerce' ),
					'type' => 'textarea',
					'description' => __( 'This controls the description which the user sees during checkout. With multiple languages do not change the default string, make your changes to the vivawallet-for-woocommerce language files.', 'vivawallet-for-woocommerce' ),
					'default' => __( 'Pay via Vivawallet - you can pay with your credit card.', 'vivawallet-for-woocommerce' )
				),
				'vivawallet_merchantid' => array
				(
					'title' => __('Merchant ID', 'vivawallet-for-woocommerce'),
					'type' => 'text',
					'description' => __('Merchant ID provided by Vivawallet.', 'vivawallet-for-woocommerce'),
					'default' => ''
				),
				'vivawallet_merchantpass' => array
				(
					'title' => __('API Key', 'vivawallet-for-woocommerce'),
					'type' => 'text',
					'description' => __('API Key provided by Vivawallet.', 'vivawallet-for-woocommerce'),
					'default' => ''
				),
				'vivawallet_source' => array
				(
					'title' => __('Source Code', 'vivawallet-for-woocommerce'),
					'type' => 'text',
					'description' => __('Vivawallet Source Code.', 'vivawallet-for-woocommerce'),
					'default' => ''
				),
				'vivawallet_instal' => array
				(
					'title' => __('Instalments', 'vivawallet-for-woocommerce'),
					'type' => 'text',
					'description' => __('Example: 90:3,180:6<br>Order total 90 euro -> allow 0 and 3 instalments<br>Order total 180 euro -> allow 0, 3 and 6 instalments<br>Leave empty in case you do not want to offer instalments.', 'vivawallet-for-woocommerce'),
					'default' => ''
				),
				'testmode' => array(
					'title' => __('Test mode', 'vivawallet-for-woocommerce'),
					'type' => 'checkbox', 
					'label' => __('Enable test mode', 'vivawallet-for-woocommerce'),
					'description' => __('Check this box for operating in test mode', 'vivawallet-for-woocommerce'),
					'default' => 'no'
				),
				'vivawallet_processing' => array(
					'title' => __('Order status', 'vivawallet-for-woocommerce'),
					'default' => 'completed',
					'type' => 'select', 
					'options' => array(
					  'completed'       => __( 'Completed', 'vivawallet-for-woocommerce' ),
					  'processing'  	=> __( 'Processing', 'vivawallet-for-woocommerce' ),
					  'on-hold'  	  	=> __( 'On hold', 'vivawallet-for-woocommerce' )
					)
				)
			);
	}

	/**
	* There are no payment fields for sprypay, but we want to show the description if set.
	**/
	function payment_fields()
	{
		if( isset($this->description) && $this->description!=''){
		echo '<p>'.__('Pay via Vivawallet - you can pay with your credit card.', 'vivawallet-for-woocommerce').'</p>';
		}
	}
	/**
	* Generate the dibs button link
	**/
	public function generate_form($order_id)
	{
		global $woocommerce, $wpdb;

		$order = new WC_Order( $order_id );
		
		if ($this->vivawallet_testmode == 'yes') {
			$action_adr = "http://demo.vivapayments.com/web/newtransaction.aspx";
			$curl_adr   = "http://demo.vivapayments.com/api/orders";
		} else {
			$action_adr = "https://www.vivapayments.com/web/newtransaction.aspx";
			$curl_adr	= "https://www.vivapayments.com/api/orders";
		}

		$mref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
		$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
		$current_version = get_option( 'woocommerce_version', null );
		if (version_compare( $current_version, '3.0.0', '>=' )) {
		define( 'WOOCOMMERCE_CHECKOUT', true );
		WC()->cart->calculate_totals();
		$amountcents = round(WC()->cart->total * 100);
		$charge = number_format(WC()->cart->total, '2', '.', '');
		} else {
		$amountcents = round($order->order_total * 100);
		$charge = number_format($order->order_total, '2', '.', '');
		}
		
		if($amountcents==0){
		$order_id = wc_get_order_id_by_order_key($_GET['key']);
		$order    = wc_get_order( $order_id );
		$amountcents = round($order->get_total() * 100);
		$charge = number_format($order->get_total(), '2', '.', '');
		} 
		
		$trlang = get_locale();
		
		if (preg_match("/gr/i", $trlang) || preg_match("/el/i", $trlang)) {
		$formlang = 'el-GR';
		} else {
		$formlang = 'en-US';
		}
		
	$MerchantID =  $this->vivawallet_merchantid;
	$Password =   html_entity_decode($this->vivawallet_merchantpass);
	
	$poststring['Amount'] = $amountcents;
	$poststring['RequestLang'] = $formlang;
	
	if (version_compare( $current_version, '3.0.0', '>=' )) {
	$customer_mail = $order->get_billing_email();
	} else {
	$customer_mail = $order->billing_email;
	}
		
	$poststring['Email'] = $customer_mail;
	
	$maxperiod = '1';
	 $installogic = $this->vivawallet_instal;
	 if(isset($installogic) && $installogic!=''){
	 $split_instal_vivawallet = explode(',',$installogic);
	 $c = count($split_instal_vivawallet);	
	 $instal_vivawallet_max = array();
	 for($i=0; $i<$c; $i++){
		list($instal_amount, $instal_term) = explode(":", $split_instal_vivawallet[$i]);
		if($charge >= $instal_amount){
		$instal_vivawallet_max[] = trim($instal_term);
		}
	}
	if(count($instal_vivawallet_max) > 0){
	 $maxperiod = max($instal_vivawallet_max);
	}
	} 	
	
		$currency_symbol ='';
		$currency_code = get_woocommerce_currency();
		switch ($currency_code) {
		case 'EUR':
   		$currency_symbol = 978;
   		break;
		case 'GBP':
   		$currency_symbol = 826;
   		break;
		case 'BGN':
   		$currency_symbol = 975;
   		break;
		case 'RON':
   		$currency_symbol = 946;
   		break;
		default:
        $currency_symbol = 978;
		}
	
	
	$poststring['MaxInstallments'] = $maxperiod;
	$poststring['MerchantTrns'] = $order_id;
	$poststring['SourceCode'] = $this->vivawallet_source;
	$poststring['CurrencyCode'] = $currency_symbol;
	$poststring['PaymentTimeOut'] = '300';

	$curl = curl_init($curl_adr);

	if (preg_match("/https/i", $curl_adr)) {
	curl_setopt($curl, CURLOPT_PORT, 443);
	}
	
	$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($poststring['MaxInstallments']).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&CurrencyCode='.urlencode($poststring['CurrencyCode']).'&PaymentTimeOut=300';
	
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
	$curlversion = curl_version();
	if(!preg_match("/NSS/" , $curlversion['ssl_version'])){
	curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, "TLSv1");
	}
	
	// execute curl
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
		
		try {
		if (version_compare(PHP_VERSION, '5.3.99', '>=')) {
		$resultObj=json_decode($response, false, 512, JSON_BIGINT_AS_STRING);
		} else {
		$response = preg_replace('/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/', ': "$1"', $response, 1);
		$resultObj = json_decode($response);
		}
		} catch( Exception $e ) {
			throw new Exception("Result is not a json object (" . $e->getMessage() . ")");
		}
		
		if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
		$OrderCode = $resultObj->OrderCode;
		$ErrorCode = $resultObj->ErrorCode;
		$ErrorText = $resultObj->ErrorText;
		}
		else{
			throw new Exception("Unable to create order code (" . $resultObj->ErrorText . ")");
		}	
		
		$query = "insert into {$wpdb->prefix}vivawallet_data (ref, ordercode, email, orderid, total_cost, currency, order_state, timestamp) values ('".$mref."', '".$OrderCode."','". $customer_mail ."','". $order_id . "',$amountcents,'978','I', now())";
	    $wpdb->query($query);
			
		$args = array
			(
				'Ref' => $OrderCode,
			);

		$paypal_args = apply_filters('woocommerce_vivawallet_args', $args);

		$args_array = array();

		foreach ($args as $key => $value)
		{
			$args_array[] = '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'" />';
		}

		$current_version = get_option( 'woocommerce_version', null );
		if (version_compare( $current_version, '2.3.0', '<' )) { //older version
		
		$woocommerce->add_inline_js( '
			jQuery("body").block({
					message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />'.__( 'Thank you for your order. We are now redirecting you to make your payment.', 'vivawallet-for-woocommerce' ).'",
					overlayCSS:
					{
						background: "#fff",
						opacity: 0.6
					},
					css: {
				        padding:        20,
				        textAlign:      "center",
				        color:          "#555",
				        border:         "3px solid #aaa",
				        backgroundColor:"#fff",
				        cursor:         "wait",
				        lineHeight:		"32px"
				    }
				});
			jQuery("#submit_vivawallet_payment_form").click();
		' ); 
		
		} else {
		
		wc_enqueue_js( '
			jQuery("body").block({
					message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />'.__( 'Thank you for your order. We are now redirecting you to make your payment.', 'vivawallet-for-woocommerce' ).'",
					overlayCSS:
					{
						background: "#fff",
						opacity: 0.6
					},
					css: {
				        padding:        20,
				        textAlign:      "center",
				        color:          "#555",
				        border:         "3px solid #aaa",
				        backgroundColor:"#fff",
				        cursor:         "wait",
				        lineHeight:		"32px"
				    }
				});
			jQuery("#submit_vivawallet_payment_form").click();
		' ); 
		}
		
		return
			'<form action="'.esc_url($action_adr).'" method="GET" id="vivawallet_payment_form">'."\n".
			implode("\n", $args_array)."\n".
			'<input type="submit" class="button alt" id="submit_vivawallet_payment_form" value="'.__('Pay Now', 'vivawallet-for-woocommerce').'" /> <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel', 'vivawallet-for-woocommerce').'</a>'."\n".
			'</form>';
	}
	
	/**
	 * Process the payment and return the result
	 **/
	function process_payment($order_id)
	{
		$order = new WC_Order($order_id);

		$current_version = get_option( 'woocommerce_version', null );
		if (version_compare( $current_version, '2.2.0', '<' )) { //older version
			return array
				(
					'result' => 'success',
					'redirect'	=> esc_url_raw(add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay')))))
				);
		} elseif (version_compare( $current_version, '2.4.0', '<' )) { //older version
			return array
				(
					'result' => 'success',
					'redirect'	=> esc_url_raw(add_query_arg('order-pay', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay')))))
				);
		} elseif (version_compare( $current_version, '3.0.0', '<' )) { //older version
			return array
				(
					'result' => 'success',
					'redirect'	=> esc_url_raw(add_query_arg('order-pay', $order->id, add_query_arg('key', $order->order_key, wc_get_page_permalink( 'checkout' ))))
				);
		} else {
			return array
			(
				'result' => 'success',
				'redirect'	=> esc_url_raw(add_query_arg('order-pay', $order->get_id(), add_query_arg('key', $order->get_order_key(), wc_get_page_permalink( 'checkout' ))))
			);
		}
	}
	
	/**
	* receipt_page
	**/
	function receipt_page($order)
	{
		echo '<p>'.__('Thank you for your order, please click the button below to pay.', 'vivawallet-for-woocommerce').'</p>';
		echo $this->generate_form($order);
	}
	
	
	/**
	* Check Response
	**/
	function check_ipn_response()
	{
		global $woocommerce, $wpdb;

		if(preg_match("/success/i", $_SERVER['REQUEST_URI']) && preg_match("/vivawallet/i", $_SERVER['REQUEST_URI']))
		{
			$tm_ref = $_GET['s'];
			$statustr = $this->vivawallet_processing;
	  
			$check_query = $wpdb->get_results("SELECT order_state, orderid FROM {$wpdb->prefix}vivawallet_data WHERE ordercode = '".addslashes($tm_ref)."'", ARRAY_A);
			$check_query_count = count($check_query);
			if($check_query_count >= 1){
			if($check_query[0]['order_state']=='I' || $check_query[0]['order_state']=='P') {
			
			$inv_id = $check_query[0]['orderid'];
			$order = new WC_Order($inv_id);
			
			if($check_query[0]['order_state']=='I'){
			$query = "update {$wpdb->prefix}vivawallet_data set order_state='P' where ordercode='".addslashes($tm_ref)."'";
		    $wpdb->query($query);
			$order->update_status($statustr, __('Order has been paid with Viva, TxID: ' . $tm_ref, 'vivawallet-for-woocommerce'));
			$order->reduce_order_stock();
			
			add_post_meta( $inv_id, '_paid_date', current_time('mysql'), true );
			//add_post_meta( $inv_id, '_transaction_id', $tm_ref, true );
			update_post_meta( $inv_id, '_transaction_id', wc_clean($tm_ref) );
			
			$order->payment_complete(wc_clean($tm_ref));
			$woocommerce->cart->empty_cart();
			}
			
			$current_version = get_option( 'woocommerce_version', null );
			if (version_compare( $current_version, '2.1.0', '<' )) { //older version
			wp_redirect(esc_url_raw(add_query_arg('key', $order->order_key, add_query_arg('order', $inv_id, get_permalink(get_option('woocommerce_thanks_page_id'))))));
			} elseif (version_compare( $current_version, '3.0.0', '<' )) { //older version
			wp_redirect(esc_url_raw(add_query_arg('key', $order->order_key, add_query_arg('order-received', $inv_id, $this->get_return_url($order)))));
			} else {
			wp_redirect(esc_url_raw(add_query_arg('key', $order->get_order_key(), add_query_arg('order-received', $inv_id, $this->get_return_url($order)))));
			}
			exit;
			
			}
          }
		}
		
		if(preg_match("/webhook/i", $_SERVER['REQUEST_URI']) && preg_match("/vivawallet/i", $_SERVER['REQUEST_URI']))
		{
			
			$postdata = file_get_contents("php://input");

			$MerchantID =  $this->vivawallet_merchantid;
			$Password =   html_entity_decode($this->vivawallet_merchantpass);
			
			if ($this->vivawallet_testmode == 'yes') {
			$curl_adr 	= 'http://demo.vivapayments.com/api/messages/config/token/';
			$posturl = 'http://demo.vivapayments.com/api/messages/config/token/';
			} else {
			$curl_adr 	= 'https://www.vivapayments.com/api/messages/config/token/';
			$posturl = 'https://www.vivapayments.com/api/messages/config/token/';
			}
		
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
	  
			$check_query = $wpdb->get_results("SELECT order_state, orderid FROM {$wpdb->prefix}vivawallet_data WHERE ordercode = '".addslashes($OrderCode)."'", ARRAY_A);
			$check_query_count = count($check_query);
			if($check_query_count >= 1){
			if($check_query[0]['order_state']=='I' && $StatusId=='F') {
			
			$query = "update {$wpdb->prefix}vivawallet_data set order_state='P' where ordercode='".addslashes($OrderCode)."'";
		    $wpdb->query($query);
			
			$inv_id = $check_query[0]['orderid'];
			$order = new WC_Order($inv_id);
			$order->update_status($statustr, __('Order has been paid with Viva, TxID: ' . $OrderCode, 'vivawallet-for-woocommerce'));
			$order->reduce_order_stock();
			
			add_post_meta( $inv_id, '_paid_date', current_time('mysql'), true );
			//add_post_meta( $inv_id, '_transaction_id', $tm_ref, true );
			update_post_meta( $inv_id, '_transaction_id', wc_clean($tm_ref) );
			
			$order->payment_complete(wc_clean($tm_ref));
			$woocommerce->cart->empty_cart();
			$current_version = get_option( 'woocommerce_version', null );
			exit;
			 }
			}
          	     } else {
		  	exit();
		  	}
		}		
				
		if(preg_match("/fail/i", $_SERVER['REQUEST_URI']) && preg_match("/vivawallet/i", $_SERVER['REQUEST_URI']))
		{
			$tm_ref = $_GET['s'];
			
			$check_query = $wpdb->get_results("SELECT orderid FROM {$wpdb->prefix}vivawallet_data WHERE ordercode = '".addslashes($tm_ref)."'", ARRAY_A);
			$check_query_count = count($check_query);
			if($check_query_count >= 1){
			
			$query = "update {$wpdb->prefix}vivawallet_data set order_state='F' where ordercode='".addslashes($tm_ref)."'";
		    $wpdb->query($query);
			
			$inv_id = $check_query[0]['orderid'];
			$order = new WC_Order($inv_id);
			//$order->update_status('failed', __('Payment failed', 'vivawallet-for-woocommerce'));
			
			$current_version = get_option( 'woocommerce_version', null );
			if (version_compare( $current_version, '2.3.0', '<' )) { //older version
			$woocommerce->add_error(__('An error occured, please try again.', 'vivawallet-for-woocommerce'));
			} else {
			wc_add_notice(__('An error occured, please try again.', 'vivawallet-for-woocommerce'), 'error' );
			}
			
			if (version_compare( $current_version, '2.6.0', '<' )) { //older version
			wp_redirect($order->get_checkout_payment_url());
			} else {
			wp_redirect(wc_get_checkout_url());
			}
			exit;
          }
		}

	}
}

/**
 * Add the gateway to WooCommerce
 **/
function add_vivawallet_gateway($methods)
{
	$methods[] = 'WC_VIVAWALLET';
	return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_vivawallet_gateway');
}

?>
