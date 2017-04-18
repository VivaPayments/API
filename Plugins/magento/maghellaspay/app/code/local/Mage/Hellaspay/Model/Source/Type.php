<?php

class Mage_Hellaspay_Model_Source_Type
{
	
	public function toOptionArray()
    {
		return array(
			array('value' => '0', 'label' => Mage::helper('hellaspay')->__('WebCheckout')),
			array('value' => '1', 'label' => Mage::helper('hellaspay')->__('Electronic Receipt')),
			array('value' => '2', 'label' => Mage::helper('hellaspay')->__('WebCheckout and Receipt'))
		);
		
	}
}

?>