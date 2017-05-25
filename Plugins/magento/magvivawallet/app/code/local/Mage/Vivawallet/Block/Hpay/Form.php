<?php
class Mage_Vivawallet_Block_Hpay_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('vivawallet/hpay/form.phtml');
        parent::_construct();
    }

    public function getQuote()
    {
        //return Mage::getSingleton('checkout/session')->getQuote();
		return Mage::getModel('checkout/session')->getQuote();
    }
	
		
    public function displayInstallments()
    {
		
		$instal_vivawallet = array();
		$split_instal_vivawallet = explode(',', $this->getMethod()->getConfigData('Installments'));

		$qtotal = $this->getQuote()->getGrandTotal();
		

		$c = count ($split_instal_vivawallet);
		
		$instal_vivawallet[] = array('0' =>  $this->__('No installments'));

		for($i=0; $i<$c; $i++)
		{
		
		list($instal_amount, $instal_term) = explode(":", $split_instal_vivawallet[$i]);
		
		if($qtotal >= $instal_amount){
		
		$install_tail = $instal_amount / $instal_term;
		$instal_vivawallet[] = array($instal_term =>  $instal_term . ' x ' . $install_tail . $this->__(' installments'));
		
		
		}
		}


        return $instal_vivawallet;
    }
	
    protected function _toHtml()
    {
        Mage::dispatchEvent('vivawallet_form_block_to_html_before', array(
            'block'     => $this
        ));
        return parent::_toHtml();
    }		
	
}
