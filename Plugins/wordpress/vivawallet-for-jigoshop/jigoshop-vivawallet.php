<?php
/*
Plugin Name: Jigoshop Vivawallet Gateway
Plugin URI: http://www.vivawallet.com/
Description: Extends Jigoshop with the Vivawallet gateway.
Version: 1.0.0
Author: Viva Wallet
Author URI: http://www.vivawallet.com/
Domain Path: /languages
*/

/*  Copyright 2017  Vivawallet.com
 *****************************************************************************
 * @category   Checkout
 * @package    Jigoshop
 * @author     Viva Wallet
 * @copyright  Copyright (c)2017 Vivawallet
 * @License    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 ******************************************************************************
*/

/* Add a custom payment class to JG
  ------------------------------------------------------------ */
function vivawallet_fallback_notice() {
    $message = '<div class="error">';
        $message .= '<p>' . __( 'JigoShop Viva Gateway depends on <a href="http://wordpress.org/extend/plugins/jigoshop/">JigoShop</a> to work!' , 'vivawallet-for-jigoshop' ) . '</p>';
    $message .= '</div>';

    echo $message;
}

add_action( 'plugins_loaded', 'vivawallet_gateway_load', 0 );

function vivawallet_gateway_load() {

if ( !class_exists( 'jigoshop_payment_gateway' ) ) {
	add_action( 'admin_notices', 'vivawallet_fallback_notice' );

	return;
}

load_plugin_textdomain( 'vivawallet-for-jigoshop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

add_filter( 'jigoshop_payment_gateways', 'add_vivawallet_gateway', 1 );

function add_vivawallet_gateway( $methods ) {
	$methods[] = 'vivawallet';
	return $methods;
}

class vivawallet extends jigoshop_payment_gateway {
private $allowed_currency = array( 'EUR','GBP','BGN','RON' );

	public function __construct()
	{

		parent::__construct();

		$options = Jigoshop_Base::get_options();

		$this->id = 'vivawallet';
		$this->icon = plugins_url( 'vivawallet.png', __FILE__ );
		$this->has_fields = false;
		$this->enabled = $options->get_option('jigoshop_vivawallet_is_enabled');
		$this->title = $options->get_option('jigoshop_vivawallet_method_title');
		$this->description = $options->get_option('jigoshop_vivawallet_description');
		$this->testmode = $options->get_option('jigoshop_vivawallet_test_mode');
		$this->vivawallet_merchantid = $options->get_option('jigoshop_vivawallet_merchantid');
		$this->vivawallet_merchantpass = html_entity_decode($options->get_option('jigoshop_vivawallet_merchantpass'));
		$this->vivawallet_source = $options->get_option('jigoshop_vivawallet_source');
		$this->vivawallet_instal = $options->get_option('jigoshop_vivawallet_instal');
		$this->currency = $options->get_option('jigoshop_currency');

		add_action('receipt_vivawallet', array($this, 'receipt_page'));
		add_action('valid-vivawallet-standard-ipn-request', array($this, 'successful_request'));
		add_action('admin_notices', array($this, 'vivawallet_notices'));
		add_action('jigoshop_api_js_gateway_vivawallet', array($this, 'check_ipn_response'));

	}

	public function vivawallet_notices()
	{
		global $wpdb;
		$query = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "vivawallet_data (id int(11) unsigned NOT NULL AUTO_INCREMENT, ref varchar(100) DEFAULT NULL, ordercode varchar(255) DEFAULT NULL, email varchar(150) DEFAULT NULL, orderid varchar(100) DEFAULT NULL, total_cost int(11) DEFAULT NULL, currency char(3) DEFAULT NULL, tm_password varchar(100) DEFAULT NULL, order_state char(1) DEFAULT NULL, sessionid varchar(32) DEFAULT NULL, timestamp datetime DEFAULT NULL, PRIMARY KEY (id))";
		$wpdb->query($query);

		$options = Jigoshop_Base::get_options();

		if ($this->enabled == 'no') {
			return;
		}

		if (!$this->vivawallet_source) {
			echo '<div class="error"><p>'.__('The Vivawallet gateway does not have values entered for <strong>Source Code</strong> and the gateway is set to enabled.  Please enter your credentials for this or the gateway <strong>will not</strong> be available on the Checkout.  Disable the gateway to remove this warning.', 'vivawallet-for-jigoshop').'</p></div>';
		}

		if (!in_array($this->currency, array('EUR','GBP','BGN','RON'))) {
			echo '<div class="error"><p>'.sprintf(__('The Vivawallet gateway accepts payments in currencies of %s.  Your current currency is %s.  Vivawallet won\'t work until you change the Jigoshop currency to an accepted one.  Vivawallet is <strong>currently disabled</strong> on the Payment Gateways settings tab.', 'vivawallet-for-jigoshop'), 'EUR/GBP/BGN/RON', $this->currency).'</p></div>';
			$options->set('jigoshop_vivawallet_is_enabled', 'no');
		}

		if (!$this->vivawallet_merchantid) {
			echo '<div class="error"><p>'.__('The Vivawallet gateway does not have values entered for <strong>Merchant ID</strong> and the gateway is set to enabled.  Please enter your credentials for this or the gateway <strong>will not</strong> be available on the Checkout.  Disable the gateway to remove this warning.', 'vivawallet-for-jigoshop').'</p></div>';
		}

		if (!$this->vivawallet_merchantpass) {
			echo '<div class="error"><p>'.__('The Vivawallet gateway does not have values entered for <strong>API KEY</strong> and the gateway is set to enabled.  Please enter your credentials for this or the gateway <strong>will not</strong> be available on the Checkout.  Disable the gateway to remove this warning.', 'vivawallet-for-jigoshop').'</p></div>';
		}
	}

	public function is_available()
	{
		if ($this->enabled == 'no') {
			return false;
		}

		if (!$this->vivawallet_source) {
			return false;
		}

		if (!$this->vivawallet_merchantid) {
			return false;
		}

		if (!$this->vivawallet_merchantpass) {
			return false;
		}

		if (!in_array($this->currency, array('EUR','GBP','BGN','RON'))) {
			return false;
		}

		return true;
	}

	protected function get_default_options() {
	load_plugin_textdomain('vivawallet-for-jigoshop', 'wp-content/plugins/vivawallet-for-jigoshop/languages/vivawallet-for-jigoshop-'.get_locale().'.mo');

	$defaults = array();

	$defaults[] = array(
			'name' => sprintf(__('Vivawallet %s', 'vivawallet-for-jigoshop'), '<img style="vertical-align:middle;margin-top:-4px;margin-left:10px;" src="'.plugins_url( 'vivawallet.png', __FILE__ ) .'" alt="Vivawallet">'),
			'type' => 'title',
			'desc' => sprintf(__('This module allows you to accept online payments via %s allowing customers to buy and pay with a credit card. Vivawallet is a safe, convenient and secure way for customers to buy online in one-step.  %s', 'vivawallet-for-jigoshop'), '<a href="https://www.vivawallet.com/" target="_blank">'.__('Vivawallet','vivawallet-for-jigoshop').'</a>', '<a href="https://www.vivawallet.com/en-us/signup" target="_blank">'.__('Signup for a Merchant Account','vivawallet-for-jigoshop').'</a>' )
		);

	$defaults[] = array(
			'name'		=> __('Enable Vivawallet','vivawallet-for-jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_vivawallet_enabled',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		$defaults[] = array(
			'name'		=> __('Title','vivawallet-for-jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the title which the user sees during checkout.', 'vivawallet-for-jigoshop'),
			'id' 		=> 'jigoshop_vivawallet_title',
			'std' 		=> __('Vivawallet','vivawallet-for-jigoshop'),
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Description','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the description which the user sees during checkout. With multiple languages do not change the default string, make your changes to the vivawallet-for-jigoshop language files.', 'vivawallet-for-jigoshop'),
			'id' 		=> 'jigoshop_vivawallet_description',
			'std' 		=> __('Pay via Vivawallet - you can pay with your credit card.', 'vivawallet-for-jigoshop'),
			'type' 		=> 'longtext'
		);

		$defaults[] = array(
			'name'		=> __('Merchant ID','vivawallet-for-jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Merchant ID provided by Vivawallet.', 'vivawallet-for-jigoshop'),
			'id' 		=> 'jigoshop_vivawallet_merchantid',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('API Key','vivawallet-for-jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('API Key provided by Vivawallet.', 'vivawallet-for-jigoshop'),
			'id' 		=> 'jigoshop_vivawallet_merchantpass',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Source Code','vivawallet-for-jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Vivawallet Source Code.', 'vivawallet-for-jigoshop'),
			'id' 		=> 'jigoshop_vivawallet_source',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Instalments','vivawallet-for-jigoshop'),
			'desc' 		=> '',
			'tip' 		=> 'Check this box when you want to allow interest free instalments.', 'vivawallet-for-jigoshop',
			'id' 		=> 'jigoshop_vivawallet_instal',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);

		$defaults[] = array(
			'name'		=> __('Enable Test Mode','vivawallet-for-jigoshop'),
			'desc' 		=> __('Turn on to enable the test mode.', 'vivawallet-for-jigoshop'),
			'tip' 		=> '',
			'id' 		=> 'jigoshop_vivawallet_test_mode',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'		=> __('No', 'jigoshop'),
				'yes'		=> __('Yes', 'jigoshop')
			)
		);

	 return $defaults;
	}

	function payment_fields()
	{
		if( isset($this->description) && $this->description!=''){
		echo '<p>'.__('Pay via Vivawallet - you can pay with your credit card.', 'vivawallet-for-jigoshop').'</p>';
		}
	}

	public function generate_form_vivawallet($order_id)
	{
		global $wpdb;

		$order = new jigoshop_order($order_id);

		if ($this->testmode == 'yes') {
			$action_adr = "https://demo.vivapayments.com/web/newtransaction.aspx";
		} else {
			$action_adr = "https://www.vivapayments.com/web/newtransaction.aspx";
		}

		$mref = "REF".substr(md5(uniqid(rand(), true)), 0, 9);
		$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
		$amountcents = round($order->order_total * 100);
		$trlang = get_locale();

		if (preg_match("/gr/i", $trlang) || preg_match("/el/i", $trlang)) {
		$formlang = 'el-GR';
		} else {
		$formlang = 'en-US';
		}

	$MerchantID =  $this->vivawallet_merchantid;
	$Password =   $this->vivawallet_merchantpass;


	$currency_symbol ='';
		$currency_code = $this->currency;
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

	$poststring['Amount'] = $amountcents;
	$poststring['RequestLang'] = $formlang;

	$poststring['Email'] = $order->billing_email;
	if($this->vivawallet_instal == 'no'){
	$poststring['MaxInstallments'] = '1';
	} else {
	$poststring['MaxInstallments'] = '36';
	}
	$poststring['MerchantTrns'] = $order_id;
	$poststring['SourceCode'] = $this->vivawallet_source;
	$poststring['CurrencyCode'] = $currency_symbol;
	$poststring['PaymentTimeOut'] = '300';

	if ($this->testmode == 'yes') {
	$curl = curl_init("https://demo.vivapayments.com/api/orders");
	curl_setopt($curl, CURLOPT_PORT, 443);
	} else {
	$curl = curl_init("https://www.vivapayments.com/api/orders");
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
	curl_setopt($curl, CURLOPT_PORT, 443);
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

		$query = "insert into ". $wpdb->prefix . "vivawallet_data (ref, ordercode, email, orderid, total_cost, currency, order_state, timestamp) values ('".$mref."', '".$OrderCode."','". $order->billing_email ."','". $order_id . "',$amountcents,'".$this->currency."','I', now())";
	    $wpdb->query($query);

		echo '<form name="vivawallet" id="vivawallet_place_form" action="'.esc_url($action_adr).'" method="GET">
		<input type="hidden" name="Ref" value="'.esc_attr($OrderCode).'" />
		<input type="submit" class="button alt" id="submit_vivawallet_place_form" value="'.__('Pay Now', 'vivawallet-for-jigoshop').'" /> 			        <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel', 'vivawallet-for-jigoshop').'</a>
		</form>';

		echo '<script type="text/javascript">
		//<![CDATA[
    	var paymentform = document.getElementById(\'vivawallet_place_form\');
   		window.onload = paymentform.submit();
		//]]>
		</script>';

		exit();

	}


	function process_payment($order_id)
	{
		$order = new jigoshop_order($order_id);

		return array
		(
			'result' => 'success',
			'redirect'	=>esc_url_raw(add_query_arg('order-pay', $order->id, add_query_arg('key', $order->order_key, get_permalink(jigoshop_get_page_id('pay')))))
		);
	}


	function receipt_page($order)
	{
		echo '<p>'.__('Thank you for your order, please click the button below to pay.', 'vivawallet-for-jigoshop').'</p>';
		echo $this->generate_form_vivawallet($order);
	}


	/**
	* Check Response
	**/
	function check_ipn_response()
	{
		global $wpdb;

		load_plugin_textdomain('vivawallet-for-jigoshop', 'wp-content/plugins/vivawallet-for-jigoshop/languages/vivawallet-for-jigoshop-'.get_locale().'.mo');

		if(preg_match("/success/i", $_SERVER['REQUEST_URI']) && preg_match("/vivawallet/i", $_SERVER['REQUEST_URI']))
		{
			$tm_ref = $_GET['s'];

			$check_query = $wpdb->get_results("SELECT order_state, orderid FROM ". $wpdb->prefix . "vivawallet_data WHERE ordercode = '".addslashes($tm_ref)."'", ARRAY_A);
			$check_query_count = count($check_query);
			if($check_query_count >= 1){
			if($check_query[0]['order_state']=='I' || $check_query[0]['order_state']=='P') {
			$inv_id = $check_query[0]['orderid'];
			$order = new jigoshop_order($inv_id);

			if($check_query[0]['order_state']=='I'){
			$query = "update ". $wpdb->prefix . "vivawallet_data set order_state='P' where ordercode='".addslashes($tm_ref)."'";
		    $wpdb->query($query);
			$order->add_order_note(__('Order has been paid with Viva, TxID: ' . $tm_ref, 'vivawallet-for-jigoshop'));
			jigoshop_log( "VIVA: payment authorized for Order ID: " . $order->id );
			$order->payment_complete();
			jigoshop_cart::empty_cart();
			$args = array(
				'key' => $order->order_key,
				'order' => $order->id,
			);
			}
			wp_safe_redirect( esc_url_raw(add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( jigoshop_get_page_id('thanks') ) ) ) ));
			} else {
			$order->update_status( 'on-hold', sprintf(__('Failed payment %s via Vivawallet.', 'vivawallet-for-jigoshop'), $tm_ref) );
		    jigoshop_log( "VIVA: payment failed for Order ID: " . $order->id );
			jigoshop::add_error(__('There was a problem with your payment, please try again with another card.', 'vivawallet-for-jigoshop'));
			wp_safe_redirect( esc_url_raw(add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( jigoshop_get_page_id('checkout') ) ) ) ));
			}
			exit;

			}
          }

		if(preg_match("/webhook/i", $_SERVER['REQUEST_URI']) && preg_match("/vivawallet/i", $_SERVER['REQUEST_URI']))
		{

			$postdata = file_get_contents("php://input");

			$MerchantID =  $this->vivawallet_merchantid;
			$Password =   html_entity_decode($this->vivawallet_merchantpass);

			if ($this->testmode == 'yes') {
			$action_adr = "https://demo.vivapayments.com/api/messages/config/token/";
			} else {
				$action_adr = "https://www.vivapayments.com/api/messages/config/token/";
			}

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_PORT, 443);
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
			curl_setopt($curl, CURLOPT_PORT, 443);
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
			$order = new jigoshop_order($inv_id);
			$order->add_order_note(__('Order has been paid with Viva, TxID: ' . $tm_ref, 'vivawallet-for-jigoshop'));
			jigoshop_log( "VIVA: payment authorized for Order ID: " . $order->id );
			$order->payment_complete();
			jigoshop_cart::empty_cart();
			$args = array(
				'key' => $order->order_key,
				'order' => $order->id,
			);
			exit;
			 }
			}
          }
		}

		if(preg_match("/fail/i", $_SERVER['REQUEST_URI']) && preg_match("/vivawallet/i", $_SERVER['REQUEST_URI']))
		{
			$tm_ref = $_GET['s'];

			$check_query = $wpdb->get_results("SELECT orderid FROM ". $wpdb->prefix . "vivawallet_data WHERE ordercode = '".addslashes($tm_ref)."'", ARRAY_A);
			$check_query_count = count($check_query);
			if($check_query_count >= 1){

			$query = "update ". $wpdb->prefix . "vivawallet_data set order_state='F' where ordercode='".addslashes($tm_ref)."'";
		    $wpdb->query($query);

			$inv_id = $check_query[0]['orderid'];
			$order = new jigoshop_order($inv_id);

			$order->update_status( 'on-hold', sprintf(__('Failed payment %s via Vivawallet.', 'vivawallet-for-jigoshop'), $tm_ref) );
		    jigoshop_log( "VIVA: payment failed for Order ID: " . $order->id );
			jigoshop::add_error(__('There was a problem with your payment, please try again with another card.', 'vivawallet-for-jigoshop'));
			wp_safe_redirect( esc_url_raw(add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( jigoshop_get_page_id('checkout') ) ) ) ));
			exit;
          }
		}

	}
}

}
?>
