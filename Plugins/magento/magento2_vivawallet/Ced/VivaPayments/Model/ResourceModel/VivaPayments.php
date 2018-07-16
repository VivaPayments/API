<?php

namespace Ced\VivaPayments\Model\ResourceModel;

class VivaPayments extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('vivapayments_data', 'id');
    }
}