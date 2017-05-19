<?php

namespace Ced\VivaPayments\Model\ResourceModel\VivaPayments;


class VivaPayments extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
   
    public function _construct()
    {
        $this->_init('Ced\VivaPayments\Model\VivaPayments', 'Ced\VivaPayments\Model\ResourceModel\VivaPayments');
    }
}
