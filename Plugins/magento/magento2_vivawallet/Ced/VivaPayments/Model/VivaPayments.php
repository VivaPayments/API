<?php

namespace Ced\VivaPayments\Model;

class VivaPayments extends \Magento\Framework\Model\AbstractModel
{   

    public function _construct()
    {
    
        $this->_init('Ced\VivaPayments\Model\ResourceModel\VivaPayments');
    }
}