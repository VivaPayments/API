<?php
namespace Ced\VivaPayments\Helper;

use Magento\Sales\Model\Order;

/**
 * Checkout workflow helper
 */
class Checkout
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    protected \Magento\Sales\Model\Service\InvoiceService $invoiceService;

    protected \Magento\Sales\Model\ResourceModel\Order\Invoice $invoiceResource;

    protected \Magento\Framework\DB\Transaction $dbTransaction;

    protected \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender;

    /**
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice $invoiceResource
     * @param \Magento\Framework\DB\Transaction $dbTransaction
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\ResourceModel\Order\Invoice $invoiceResource,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->session = $session;
        $this->invoiceService = $invoiceService;
        $this->invoiceResource = $invoiceResource;
        $this->dbTransaction = $dbTransaction;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Cancel last placed order with specified comment message
     *
     * @param string $comment Comment appended to order history
     * @return bool True if order cancelled, false otherwise
     */
    public function cancelCurrentOrder($comment)
    {
        $order = $this->session->getLastRealOrder();
        if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }

    /**
     * Restores quote
     *
     * @return bool
     */
    public function restoreQuote()
    {
        return $this->session->restoreQuote();
    }

    public function createInvoiceForOrder(Order $order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $this->invoiceResource->save($invoice);

            $this->dbTransaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $this->invoiceSender->send($invoice);
        }
    }
}
