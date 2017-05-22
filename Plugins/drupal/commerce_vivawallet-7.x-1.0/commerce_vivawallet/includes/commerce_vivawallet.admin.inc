<?php
/**
 * Payment method callback: settings form.
 */
function commerce_vivawallet_settings_form($settings = NULL) {
  $form = array();

  $settings = (array) $settings + array(
    'vivawallet_mid' => '',
    'vivawallet_pass' => '',
    'vivawallet_source' => '',
    'status' => array('pending'),
	'vivawallet_method_title' => 'Vivawallet Credit Card',
    'success_message' => '',
    'fail_message' => '',
  );

  $form['vivawallet_mid'] = array(
    '#type' => 'textfield',
    '#title' => t('Merchant ID'),
    '#description' => t(' Your Merchant ID'),
    '#default_value' => $settings['vivawallet_mid'],
  );

  $form['vivawallet_pass'] = array(
    '#type' => 'textfield',
    '#title' => t('API Key'),
    '#description' => t(' Your API Key'),
    '#default_value' => $settings['vivawallet_pass'],
  );

  $form['vivawallet_source'] = array(
    '#type' => 'textfield',
    '#title' => t('Source Code'),
    '#description' => t(' Viva Payments Source Code'),
    '#default_value' => $settings['vivawallet_source'],
  );

  $form['vivawallet_instalments'] = array(
    	'#type' => 'radios',
    	'#title' => t('Instalments'),
    	'#description' => t(' Allow instalments'),
    	'#options' => array(
      	'1' => t('Allow'),
      	'2' => t('Deny')
      ),
    	'#default_value' => @$settings['vivawallet_instalments'],
  	  );
	  
  $form['vivawallet_mode'] = array(
    	'#type' => 'radios',
    	'#title' => t('Transaction mode'),
    	'#description' => t(' Transaction options'),
    	'#options' => array(
      	'Live' => t('Live transactions'),
      	'Test' => t('Testing')
      ),
    	'#default_value' => @$settings['vivawallet_mode'],
  	  );	    
	  
  $form['vivawallet_currency'] = array(
        '#type' => 'select',
        '#title' => t('Currency code'),
        '#description' => t(' Transactions can only be processed in one of the listed currencies.'),
        '#options' => _commerce_vivawallet_currency_array(),
        '#default_value' => @$settings['vivawallet_currency'],
      );
	  
  $form['vivawallet_method_title'] = array(
        '#type' => 'textfield',
        '#title' => t('Payment method title'),
        '#default_value' => $settings['vivawallet_method_title'],
      );	  

  $form['vivawallet_status'] = array(
    '#type' => 'select',
    '#title' => t('Status'),
    '#description' => t('Choose order status after success transaction'),
    '#options' => commerce_vivawallet_statuses(),
    '#default_value' => @$settings['vivawallet_status'],
  );

   $form['vivawallet_success_message'] = array(
    '#type' => 'textarea',
    '#rows' => 3,
    '#title' => t('Success message'),
    '#description' => t('Enter text that will be displayed when payment succesful'),
    '#default_value' => @$settings['vivawallet_success_message'],
  );

  $form['vivawallet_fail_message'] = array(
    '#type' => 'textarea',
    '#rows' => 3,
    '#title' => t('Fail message'),
    '#description' => t('Enter text that will be displayed when payment fail'),
    '#default_value' => @$settings['vivawallet_fail_message'],
  );

  $form['#submit'][] = variable_set('commerce_vivawallet_settings', $form);

  return $form;
}