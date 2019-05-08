<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\VivaPayments\Model\Config\Source\Order\Status;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Order Status source model
 */
class Pendingpayment extends Status
{
    /**
     * @var string[]
     */
    //protected $_stateStatuses = [Order::STATE_PENDING_PAYMENT];
    //BOF Order Status
	protected $_stateStatuses = [Order::STATE_PENDING_PAYMENT, Order::STATE_PROCESSING, Order::STATE_COMPLETE, Order::STATE_CLOSED, Order::STATE_CANCELED, Order::STATE_HOLDED, Order::STATE_PAYMENT_REVIEW];
	//EOF Order Status
}
