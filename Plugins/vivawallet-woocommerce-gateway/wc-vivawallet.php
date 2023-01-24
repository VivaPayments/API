<?php
/*
Plugin Name: Viva Wallet Smart Checkout
Plugin URI: http://www.vivawallet.com/
Description: Extends WooCommerce with the Viva Wallet Smart Checkout.
Version: 3.6.8
Author: Viva Wallet
Author URI: http://www.vivawallet.com/
Text Domain: vivawallet-for-woocommerce
Domain Path: /languages
*/
/*  Copyright 2020  Vivawallet.com
 *****************************************************************************
 * @category   Payment Gateway WordPress WooCommerce
 * @package    Viva Wallet v3.6.8
 * @author     Viva Wallet
 * @copyright  Copyright (c)2020 Viva Wallet http://www.vivawallet.com/
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
            $this->icon = apply_filters('woocommerce_vivawallet_icon', ''.$plugin_dir.'cards-wallets-transfers-more.png');
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

            load_plugin_textdomain('vivawallet-for-woocommerce', false,basename( dirname( __FILE__ ) ) . '/languages');
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
            if (!in_array( get_woocommerce_currency(), array('HRK', 'CZK', 'DKK', 'HUF', 'SEK', 'GBP', 'RON', 'BGN','EUR', 'PLN'))) return false;

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
            <h3><?php _e('Viva Wallet Smart Checkout', 'vivawallet-for-woocommerce'); ?></h3>
            <p><?php _e('Viva Wallet Smart Checkout redirects customers to their secure server for making payments.', 'vivawallet-for-woocommerce'); ?></p>
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
                    'label' => __('Enable Viva Wallet Smart Checkout', 'vivawallet-for-woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array
                (
                    'title' => __('Title', 'vivawallet-for-woocommerce'),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'vivawallet-for-woocommerce' ),
                    'default' => __('Viva Wallet Smart Checkout', 'vivawallet-for-woocommerce')
                ),
                'description' => array(
                    'title' => __( 'Description', 'woocommerce' ),
                    'type' => 'textarea',
                    'description' => __( 'This controls the description which the user sees during checkout.', 'vivawallet-for-woocommerce' ),
                    'default' => __( 'Pay using 30+ methods (cards, digital wallets, local payment methods, online banking, and more)', 'vivawallet-for-woocommerce' )
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
                echo '<p>'.$this->description.'</p>';
            }
        }

        /*
         * Get default language for smart checkout
         */
        public function getRequestLanguage() {
            $supportedLanguages = [
                'bg' => 'bg-BG',
                'hr' => 'hr-HR',
                'cs' => 'cs-CZ',
                'da' => 'da-DK',
                'nl' => 'nl-NL',
                'en' => 'en-GB',
                'fi' => 'fi-FI',
                'fr' => 'fr-FR',
                'de' => 'de-DE',
                'el' => 'el-GR',
                'hu' => 'hu-HU',
                'it' => 'it-IT',
                'pl' => 'pl-PL',
                'pt' => 'pt-PT',
                'ro' => 'ro-RO',
                'es' => 'es-ES'
            ];
            $locale             = get_locale();
            if ( ! in_array( $locale, $supportedLanguages ) ) {
                if ( isset( $supportedLanguages[ $locale ] ) ) {
                    $locale = $supportedLanguages[ $locale ];
                } else {
                    foreach ( [ '_', '-' ] as $separator ) {
                        $localeParts = explode( $separator, $locale );
                        if ( isset( $supportedLanguages[ $localeParts[0] ] ) ) {
                            $locale = $supportedLanguages[ $localeParts[0] ];
                            break;
                        }
                    }
                    if ( ! in_array( $locale, $supportedLanguages ) ) {
                        $locale = 'en-GB';
                    }
                }
            }

            return $locale;
        }

        /**
         * Generate the dibs button link
         **/
        public function generate_form($order_id)
        {
            global $woocommerce, $wpdb;
            $order = new WC_Order( $order_id );

            if ($this->vivawallet_testmode == 'yes') {
                $action_adr = "https://demo.vivapayments.com/web/newtransaction.aspx";
                $curl_adr   = "https://demo.vivapayments.com/api/orders";
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
                $amountcents = round($order->get_total() * 100);
                $charge = number_format($order->get_total(), '2', '.', '');
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

            $formlang   = $this->getRequestLanguage();
            $MerchantID = $this->vivawallet_merchantid;
            $Password   = html_entity_decode( $this->vivawallet_merchantpass );

            $poststring['Amount'] = $amountcents;
            $poststring['RequestLang'] = $formlang;

            if (version_compare( $current_version, '3.0.0', '>=' )) {
                $customer_mail = $order->get_billing_email();
                $firstName = method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : '';
                $lastName = method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : '';
            } else {
                $customer_mail = $order->billing_email;
                $firstName = isset( $order->billing_first_name ) ? $order->billing_first_name : '';
                $lastName = isset( $order->billing_last_name ) ? $order->billing_last_name : '';
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
                case 'HRK':
                    $currency_symbol = 191; // CROATIAN KUNA.
                    break;
                case 'CZK':
                    $currency_symbol = 203; // CZECH KORUNA.
                    break;
                case 'DKK':
                    $currency_symbol = 208; // DANISH KRONE.
                    break;
                case 'HUF':
                    $currency_symbol = 348; // HUNGARIAN FORINT.
                    break;
                case 'SEK':
                    $currency_symbol = 752; // SWEDISH KRONA.
                    break;
                case 'GBP':
                    $currency_symbol = 826; // POUND STERLING.
                    break;
                case 'RON':
                    $currency_symbol = 946; // ROMANIAN LEU.
                    break;
                case 'BGN':
                    $currency_symbol = 975; // BULGARIAN LEV.
                    break;
                case 'EUR':
                    $currency_symbol = 978; // EURO.
                    break;
                case 'PLN':
                    $currency_symbol = 985; // POLISH ZLOTY.
                    break;
                default:
                    $currency_symbol = 978;
            }
            $site_name = get_bloginfo( 'name' );

            $body = [
                'Amount'            => $poststring['Amount'],
                'RequestLang'       => $poststring['RequestLang'],
                'Email'             => $poststring['Email'],
                'MaxInstallments'   => $maxperiod,
                'MerchantTrns'      => $order_id,
                'CustomerTrns'      => $site_name,
                'SourceCode'        => $this->vivawallet_source,
                'CurrencyCode'      => $currency_symbol,
                'DisableCash'       => 'true'
            ];
            if ( ! empty( $firstName ) && ! empty( $lastName ) ) {
                $body['FullName'] = "$firstName $lastName";
            }

            $args = [
                'body' => $body,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($MerchantID . ':' . $Password)
                ],
                'cookies' => []
            ];

            $postRequest = wp_remote_post($curl_adr, $args);

            if (is_array($postRequest) && $postRequest['response']['code'] === 200) {
                $body = json_decode($postRequest['body'], true, 512, JSON_BIGINT_AS_STRING);
            } else {
                if ( is_wp_error( $postRequest ) ) {
                    $code = $postRequest->get_error_code();
                    $message = $postRequest->get_error_message();
                } else {
                    $code = $postRequest['response']['code'];
                    $message = $postRequest['response']['message'];
                }
                error_log(__METHOD__ . PHP_EOL . 'Code:' . $code . PHP_EOL. ' Error:' . $message);
                throw new Exception("Unable to reach Viva Payments ($code -- $message)");
            }

            if ($body['ErrorCode'] === 0) {
                $OrderCode = $body['OrderCode'];
                $ErrorCode = $body['ErrorCode'];
                $ErrorText = $body['ErrorText'];
            } else {
                throw new Exception("Unable to create order code (" . $body['ErrorText'] . ")");
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
            if (version_compare( $current_version, '2.3.0', '<' )) { //older version

                $woocommerce->add_inline_js( '
			jQuery("body").block({
					message: "'.__( 'Thank you for your order. We are now redirecting you to make your payment.', 'vivawallet-for-woocommerce' ).'",
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
					message: "'.__( 'Thank you for your order. We are now redirecting you to make your payment.', 'vivawallet-for-woocommerce' ).'",
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
            $current_version = get_option( 'woocommerce_version', null );
            if(preg_match("/success/i", $_SERVER['REQUEST_URI']) && preg_match("/vivawallet/i", $_SERVER['REQUEST_URI']))
            {
                $tm_ref = $_GET['s'];
                $tm_ref = sanitize_text_field($tm_ref);
                $statustr = $this->vivawallet_processing;

                //Retrieve order data
                $MerchantID =  $this->vivawallet_merchantid;
                $Password =   html_entity_decode($this->vivawallet_merchantpass);

                if ($this->vivawallet_testmode == 'yes') {
                    $geturl = 'https://demo.vivapayments.com/api/orders/' . $tm_ref;
                } else {
                    $geturl = 'https://www.vivapayments.com/api/orders/' . $tm_ref;
                }

                $body = [];

                $args = [
                    'body' => $body,
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($MerchantID . ':' . $Password)
                    ],
                    'cookies' => []
                ];

                $orderRequest = wp_remote_get($geturl, $args);

                if($orderRequest['response']['code'] === 200) {
                    $body = json_decode($orderRequest['body'], true, 512, JSON_BIGINT_AS_STRING);
                }
                //End retrieve order data

                $check_query = $wpdb->get_results("SELECT order_state, orderid FROM {$wpdb->prefix}vivawallet_data WHERE ordercode = '".addslashes($tm_ref)."'", ARRAY_A);
                $check_query_count = count($check_query);
                if($check_query_count >= 1){
                    if( ($check_query[0]['order_state']=='I' || $check_query[0]['order_state']=='P') && 3 === $body['StateId'] ) {

                        $inv_id = $check_query[0]['orderid'];
                        $order = new WC_Order($inv_id);

                        if($check_query[0]['order_state']=='I'){
                            $query = "update {$wpdb->prefix}vivawallet_data set order_state='P' where ordercode='".addslashes($tm_ref)."'";
                            $wpdb->query($query);
                            $order->update_status($statustr, __('Order has been paid with Viva Wallet Smart Checkout, TxID: ' . $tm_ref, 'vivawallet-for-woocommerce'));

                            if (version_compare( $current_version, '3.0.0', '<' )) {
                                $order->reduce_order_stock();
                            } else {
                                wc_reduce_stock_levels( $order->get_id() );
                            }

                            add_post_meta( $inv_id, '_paid_date', current_time('mysql'), true );
                            //add_post_meta( $inv_id, '_transaction_id', $tm_ref, true );
                            update_post_meta( $inv_id, '_transaction_id', wc_clean($tm_ref) );

                            $order->payment_complete(wc_clean($tm_ref));
                            $woocommerce->cart->empty_cart();
                        }

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
                    $posturl = 'https://demo.vivapayments.com/api/messages/config/token/';
                } else {
                    $posturl = 'https://www.vivapayments.com/api/messages/config/token/';
                }

                $body = [];

                $args = [
                    'body' => $body,
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($MerchantID . ':' . $Password)
                    ],
                    'cookies' => []
                ];

                $postRequest = wp_remote_get($posturl, $args);

                if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
                    echo $postRequest['body'];
                    exit;
                }

                $eventData = false;
                $eventTypeId = false;

                if ($postRequest['response']['code'] === 200) {
                    $postDataArray = json_decode($postdata, true, 512, JSON_BIGINT_AS_STRING);
                    if (isset($postDataArray['EventData'])) {
                        $eventData = $postDataArray['EventData'];
                    }
                    if (isset($postDataArray['EventTypeId'])) {
                        $eventTypeId = $postDataArray['EventTypeId'];
                    }
                } else {
                    error_log(__METHOD__ . PHP_EOL . 'Code:' . $postRequest['response']['code'] . PHP_EOL. ' Error:' . $postRequest['response']['message']);
                    throw new Exception("Unable to reach Viva Payments (" . $postRequest['response']['message'] . ")");
                }

                if($eventData !== false && 1796 === $eventTypeId) {
                    $StatusId = $eventData['StatusId'];
                    $OrderCode = sanitize_text_field($eventData['OrderCode']);
                    $statustr = $this->vivawallet_processing;

                    //Retrieve order data
                    if ($this->vivawallet_testmode == 'yes') {
                        $geturl = 'https://demo.vivapayments.com/api/orders/' . $OrderCode;
                    } else {
                        $geturl = 'https://www.vivapayments.com/api/orders/' . $OrderCode;
                    }
                    $body = [];

                    $args = [
                        'body' => $body,
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode($MerchantID . ':' . $Password)
                        ],
                        'cookies' => []
                    ];

                    $orderRequest = wp_remote_get($geturl, $args);

                    if($orderRequest['response']['code'] === 200) {
                        $body = json_decode($orderRequest['body'], true, 512, JSON_BIGINT_AS_STRING);
                    }
                    //End retrieve order data

                    $check_query = $wpdb->get_results("SELECT order_state, orderid FROM {$wpdb->prefix}vivawallet_data WHERE ordercode = '".addslashes($OrderCode)."'", ARRAY_A);
                    $check_query_count = count($check_query);
                    if($check_query_count >= 1){
                        if($check_query[0]['order_state']=='I' && $StatusId=='F' && 3 === $body['StateId']) {

                            $query = "update {$wpdb->prefix}vivawallet_data set order_state='P' where ordercode='".addslashes($OrderCode)."'";
                            $wpdb->query($query);

                            $inv_id = $check_query[0]['orderid'];
                            $order = new WC_Order($inv_id);
                            $order->update_status($statustr, __('Order has been paid with Viva, TxID: ' . $OrderCode, 'vivawallet-for-woocommerce'));

                            if (version_compare( $current_version, '3.0.0', '<' )) {
                                $order->reduce_order_stock();
                            } else {
                                wc_reduce_stock_levels( $order->get_id() );
                            }

                            add_post_meta( $inv_id, '_paid_date', current_time('mysql'), true );
                            //add_post_meta( $inv_id, '_transaction_id', $tm_ref, true );
                            update_post_meta( $inv_id, '_transaction_id', wc_clean($tm_ref) );

                            $order->payment_complete(wc_clean($tm_ref));
                            $woocommerce->cart->empty_cart();
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
                $tm_ref = sanitize_text_field($tm_ref);

                $check_query = $wpdb->get_results("SELECT orderid FROM {$wpdb->prefix}vivawallet_data WHERE ordercode = '".addslashes($tm_ref)."'", ARRAY_A);
                $check_query_count = count($check_query);
                if($check_query_count >= 1){

                    $query = "update {$wpdb->prefix}vivawallet_data set order_state='F' where ordercode='".addslashes($tm_ref)."'";
                    $wpdb->query($query);

                    $inv_id = $check_query[0]['orderid'];
                    $order = new WC_Order($inv_id);
                    //$order->update_status('failed', __('Payment failed', 'vivawallet-for-woocommerce'));

                    if (version_compare( $current_version, '2.3.0', '<' )) { //older version
                        $woocommerce->add_error(__('There was a problem with your payment, please try again with another card.', 'vivawallet-for-woocommerce'));
                    } else {
                        wc_add_notice(__('There was a problem with your payment, please try again with another card.', 'vivawallet-for-woocommerce'), 'error' );
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
