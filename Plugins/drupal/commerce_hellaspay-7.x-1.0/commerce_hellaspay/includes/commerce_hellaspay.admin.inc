<?php
/**
 * Payment method callback: settings form.
 */
function commerce_hellaspay_settings_form($settings = NULL) {
  $form = array();

  $settings = (array) $settings + array(
    'hellaspay_mid' => '',
    'hellaspay_pass' => '',
    'hellaspay_source' => '',
    'status' => array('pending'),
	'hellaspay_method_title' => 'Hellas Pay Credit Card',
    'success_message' => '',
    'fail_message' => '',
  );

  $form['hellaspay_mid'] = array(
    '#type' => 'textfield',
    '#title' => t('Merchant ID'),
    '#description' => t(' Your Merchant ID'),
    '#default_value' => $settings['hellaspay_mid'],
  );

  $form['hellaspay_pass'] = array(
    '#type' => 'textfield',
    '#title' => t('API Key'),
    '#description' => t(' Your API Key'),
    '#default_value' => $settings['hellaspay_pass'],
  );

  $form['hellaspay_source'] = array(
    '#type' => 'textfield',
    '#title' => t('Source Code'),
    '#description' => t(' Viva Payments Source Code'),
    '#default_value' => $settings['hellaspay_source'],
  );

  $form['hellaspay_instalments'] = array(
    	'#type' => 'radios',
    	'#title' => t('Instalments'),
    	'#description' => t(' Allow instalments'),
    	'#options' => array(
      	'1' => t('Allow'),
      	'2' => t('Deny')
      ),
    	'#default_value' => @$settings['hellaspay_instalments'],
  	  );
	  
  $form['hellaspay_mode'] = array(
    	'#type' => 'radios',
    	'#title' => t('Transaction mode'),
    	'#description' => t(' Transaction options'),
    	'#options' => array(
      	'Live' => t('Live transactions'),
      	'Test' => t('Testing')
      ),
    	'#default_value' => @$settings['hellaspay_mode'],
  	  );	    
	  
  $form['hellaspay_currency'] = array(
        '#type' => 'select',
        '#title' => t('Currency code'),
        '#description' => t(' Transactions can only be processed in one of the listed currencies.'),
        '#options' => _commerce_hellaspay_currency_array(),
        '#default_value' => @$settings['hellaspay_currency'],
      );
	  
  $form['hellaspay_method_title'] = array(
        '#type' => 'textfield',
        '#title' => t('Payment method title'),
        '#default_value' => $settings['hellaspay_method_title'],
      );	  

  $form['hellaspay_status'] = array(
    '#type' => 'select',
    '#title' => t('Status'),
    '#description' => t('Choose order status after success transaction'),
    '#options' => commerce_hellaspay_statuses(),
    '#default_value' => @$settings['hellaspay_status'],
  );

   $form['hellaspay_success_message'] = array(
    '#type' => 'textarea',
    '#rows' => 3,
    '#title' => t('Success message'),
    '#description' => t('Enter text that will be displayed when payment succesful'),
    '#default_value' => @$settings['hellaspay_success_message'],
  );

  $form['hellaspay_fail_message'] = array(
    '#type' => 'textarea',
    '#rows' => 3,
    '#title' => t('Fail message'),
    '#description' => t('Enter text that will be displayed when payment fail'),
    '#default_value' => @$settings['hellaspay_fail_message'],
  );

  $form['#submit'][] = variable_set('commerce_hellaspay_settings', $form);

  return $form;
}