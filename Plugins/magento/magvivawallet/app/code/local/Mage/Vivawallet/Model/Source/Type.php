<?php

class Mage_Vivawallet_Model_Source_Type
{
	
	public function toOptionArray()
    {
		return array(
			array('value' => '0', 'label' => Mage::helper('vivawallet')->__('WebCheckout')),
			array('value' => '1', 'label' => Mage::helper('vivawallet')->__('Electronic Receipt')),
			array('value' => '2', 'label' => Mage::helper('vivawallet')->__('WebCheckout and Receipt'))
		);
		
	}
}

?>