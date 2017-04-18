<?php

class Mage_Hellaspay_Model_Source_Currencies
{

	public function toOptionArray()
    {

		return array(
			array('value' => 'EUR', 'label' => Mage::helper('hellaspay')->__('Euro'))
		);

	}
}

?>