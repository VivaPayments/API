<?php

class Mage_Hellaspay_Block_Hpay_Redirect extends Mage_Core_Block_Abstract
{

	protected function _toHtml()
    {
        $hpay = Mage::getModel('hellaspay/hpay');

        $form = new Varien_Data_Form();
        $form->setAction($hpay->getUrl())
            ->setId('hpay_checkout')
            ->setName('hpay_checkout')
            ->setMethod('GET')
            ->setUseContainer(true);
		$form = $hpay->addHellaspayFields($form);
			
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to HellasPay in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("hpay_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}
