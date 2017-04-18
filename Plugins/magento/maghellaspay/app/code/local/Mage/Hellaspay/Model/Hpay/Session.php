<?php

class Mage_Hellaspay_Model_Hpay_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('hpay');
    }
}
