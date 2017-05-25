<?php

class Mage_Vivawallet_Model_Source_Currencies
{

	public function toOptionArray()
    {

		return array(
			array('value' => 'EUR', 'label' => Mage::helper('vivawallet')->__('Euro'))
		);

	}
}

?>