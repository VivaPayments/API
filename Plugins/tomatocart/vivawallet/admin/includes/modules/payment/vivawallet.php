<?php
/*
  $Id: vivawallet.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com
*/

/**
 * The administration side of the Vivawallet module
 */

  class osC_Payment_vivawallet extends osC_Payment_Admin {

/**
 * The administrative title of the payment module
 *
 * @var string
 * @access private
 */

    var $_title;

/**
 * The code of the payment module
 *
 * @var string
 * @access private
 */

    var $_code = 'vivawallet';

/**
 * The developers name
 *
 * @var string
 * @access private
 */

    var $_author_name = 'WEB-IT';

/**
 * The developers address
 *
 * @var string
 * @access private
 */

  var $_author_www = 'http://www.vivawallet.com';

/**
 * The status of the module
 *
 * @var boolean
 * @access private
 */

    var $_status = false;
	
/**
 * Constructor
 */

    function osC_Payment_vivawallet() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_vivawallet_title');
      $this->_description = $osC_Language->get('payment_vivawallet_description');
      $this->_method_title = $osC_Language->get('payment_vivawallet_method_title');
      $this->_status = (defined('MODULE_PAYMENT_VIVAWALLET_STATUS') && (MODULE_PAYMENT_VIVAWALLET_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_VIVAWALLET_SORT_ORDER') ? MODULE_PAYMENT_VIVAWALLET_SORT_ORDER : null);
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_VIVAWALLET_STATUS');
    }

/**
 * Installs the module
 *
 * @access public
 * @see osC_Payment_Admin::install()
 */

    function install() {
      global $osC_Database;

      parent::install();
	  
	  $osC_Database->simpleQuery("CREATE TABLE IF NOT EXISTS vivawallet_data (
	  id int(11) unsigned NOT NULL AUTO_INCREMENT,
	  OrderCode varchar(255) DEFAULT NULL,
	  ErrorCode varchar(50) DEFAULT NULL,
	  ErrorText varchar(255) DEFAULT NULL,
	  Timestamp datetime DEFAULT NULL,
	  ref varchar(150) DEFAULT NULL,
	  total_cost int(11) DEFAULT NULL,
	  currency char(3) DEFAULT NULL,
	  order_state char(1) DEFAULT NULL,
	  sessionid varchar(50) DEFAULT NULL,
	  PRIMARY KEY (id))");
	  
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable Vivawallet', 'MODULE_PAYMENT_VIVAWALLET_STATUS', '-1', 'Do you want to accept Vivawallet?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");

	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Operation Mode', 'MODULE_PAYMENT_VIVAWALLET_MODE', 'Live', 'Transaction mode to use for the service', '6', '0', 'osc_cfg_set_boolean_value(array(\'Live\', \'Testing\'))', now())");

	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MerchantID', 'MODULE_PAYMENT_VIVAWALLET_MERCHANTID', '', '', '6', '0', now())");
	  
	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Key', 'MODULE_PAYMENT_VIVAWALLET_MERCHANTPASS', '', '', '6', '0', now())");	
	  
	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Source Code', 'MODULE_PAYMENT_VIVAWALLET_SOURCE', '', '', '6', '0', now())");
	  
	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Operation Mode', 'MODULE_PAYMENT_VIVAWALLET_INSTAL', 'Allow', 'Allow free instalments', '6', '0', 'osc_cfg_set_boolean_value(array(\'Allow\', \'Deny\'))', now())");	  
	  
	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_VIVAWALLET_CURRENCY', 'EUR', 'The currency to use for credit card transactions', '6', '0', 'osc_cfg_set_boolean_value(array(\'Selected Currency\',\'EUR\'))', now())");
      
	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_VIVAWALLET_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      
	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_VIVAWALLET_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      
	  $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */
	var $_Public_key = 'Q29weXJpZ2h0IDIwMTEgLSBULkMuIHZhbiBkZXIgVmVlciAtIElCUw==';
	
    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_VIVAWALLET_STATUS',
                             'MODULE_PAYMENT_VIVAWALLET_MERCHANTID',
							 'MODULE_PAYMENT_VIVAWALLET_MERCHANTPASS',
							 'MODULE_PAYMENT_VIVAWALLET_SOURCE',
							 'MODULE_PAYMENT_VIVAWALLET_INSTAL',
							 'MODULE_PAYMENT_VIVAWALLET_MODE',
                             'MODULE_PAYMENT_VIVAWALLET_CURRENCY',
                             'MODULE_PAYMENT_VIVAWALLET_SORT_ORDER',
                             'MODULE_PAYMENT_VIVAWALLET_ZONE',
                             'MODULE_PAYMENT_VIVAWALLET_ORDER_STATUS_ID');
      }

      return $this->_keys;
    }
  }
?>