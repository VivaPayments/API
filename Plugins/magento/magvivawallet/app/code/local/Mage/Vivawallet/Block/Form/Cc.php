<?php
class Mage_Vivawallet_Block_Form_Cc extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('vivawallet/form/cc.phtml');
    }

    public function getQuote()
    {
        //return Mage::getSingleton('checkout/session')->getQuote();
		return Mage::getModel('checkout/session')->getQuote();
    }
	
		
    public function displayInstallments()
    {
		
		$instal_logic = $this->getMethod()->getConfigData('Installments');
		if(isset($instal_logic) && $instal_logic!=''){
		
		$instal_vivawallet = array();
		$split_instal_vivawallet = explode(',', $this->getMethod()->getConfigData('Installments'));

		$qtotal = $this->getQuote()->getGrandTotal();
		

		$c = count ($split_instal_vivawallet);
		
		$instal_vivawallet[] = array('0' =>  $this->__('No installments'));

		for($i=0; $i<$c; $i++)
		{
		
		list($instal_amount, $instal_term) = explode(":", $split_instal_vivawallet[$i]);
		
		if($qtotal >= $instal_amount){
		$part_amount = $qtotal / $instal_term;
		$instal_vivawallet[] = array($instal_term =>  $instal_term . $this->__(' installments'));
		}
		}


        return $instal_vivawallet;
		}
    }


    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0=>$this->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrive has verification configuration
     *
     * @return boolean
     */
    public function hasVerification()
    {
        if ($this->getMethod()) {
            $configData = $this->getMethod()->getConfigData('useccv');
            if(is_null($configData)){
                return true;
            }
            return (bool) $configData;
        }
        return true;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent('vivawallet_form_block_to_html_before', array(
            'block'     => $this
        ));
        return parent::_toHtml();
    }
}