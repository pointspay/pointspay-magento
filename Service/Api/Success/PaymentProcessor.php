<?php

namespace Pointspay\Pointspay\Service\Api\Success;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Pointspay\Pointspay\Api\IpnInterface;
use Pointspay\Pointspay\Model\Ipn;

/**
 * This class is responsible for processing the payment and creating an invoice
 * in way like IPN does it.
 * Basically, it is copying the logic from IPN to the PaymentProcessor class.
 */
class PaymentProcessor
{
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
     */
    public function __construct(
        BuilderInterface $transactionBuilder,
        OrderSender $orderSender,
        HistoryFactory $historyFactory
    ) {
        $this->transactionBuilder = $transactionBuilder;
        $this->orderSender = $orderSender;
        $this->historyFactory = $historyFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param array $gatewayData
     * @return true
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processInvoice(Order $order, array $gatewayData)
    {
        if ($order->canInvoice() && !$order->hasInvoices() && $gatewayData[Ipn::STATUS] === Ipn::PAYMENTSTATUS_COMPLETED) {
            if ($order->getState() === Order::STATE_PENDING_PAYMENT) {
                $order->setState(Order::STATE_PROCESSING);
                $order->setStatus(Order::STATE_PROCESSING);
            }
            $payment = $this->createTransaction($order, $gatewayData);
            $this->createInvoice($order, $payment);
        }
        return true;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param array $gatewayData
     * @return false|float|\Magento\Framework\DataObject|\Magento\Sales\Api\Data\OrderPaymentInterface|mixed|null
     * @throws \Exception
     */
    protected function createTransaction(Order $order, array $gatewayData)
    {
        $payment = $order->getPayment();

        $trans_id = $gatewayData[IpnInterface::PAYMENT_ID];
        $payment->setLastTransId($trans_id);
        $payment->setTransactionId($trans_id);

        $formatedPrice = $order->getBaseCurrency()->formatTxt(
            $order->getGrandTotal()
        );

        $message = __('The Captured amount is %1 (by Sale method).', $formatedPrice);
        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($trans_id)
            ->setAdditionalInformation(
                [Transaction::RAW_DETAILS => (array)$payment->getAdditionalInformation()]
            )
            ->setFailSafe(true)
            //build method creates the transaction and returns the object
            ->build(Transaction::TYPE_CAPTURE);
        $transaction->setParentTxnId($trans_id);
        $transaction->setIsClosed(0);
        $payment->addTransactionCommentsToOrder(
            $transaction,
            $message
        );
        $payment->setParentTransactionId($trans_id);

        $payment->save();
        $transaction->save();

        return $payment;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createInvoice(Order $order, OrderPaymentInterface $payment)
    {
        $paymentTransactionId = $payment->getTransactionId();
        $invoice = $order->prepareInvoice();
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->setTransactionId($paymentTransactionId);
        $invoice->register()
            ->pay();

        $invoice->save();
        // to prevent another invoice creation from IPN
        $order->setData(\Magento\Sales\Model\Order::ACTION_FLAG_INVOICE, false);
        $order->save();

        $message = __(
            'Invoiced amount of %1 Transaction ID: %2',
            $order->formatPriceTxt($payment->getAmountOrdered()),
            $paymentTransactionId
        );
        $this->addHistoryComment($order, $message);

        if (!$order->getEmailSent()) {
            $this->orderSender->send($order);
            $history = $order->addStatusHistoryComment(
                __('You notified customer about invoice #%1.', $invoice->getIncrementId())
            )
                ->setIsCustomerNotified(true);
            $history->save();
        }
    }

    /**
     * Add a comment to order history
     *
     * @param Order $order
     * @param string $message
     * @throws \Exception
     */
    protected function addHistoryComment(Order $order, $message)
    {
        /** @var \Magento\Sales\Model\Order\Status\History $history */
        $history = $this->historyFactory->create();
        $history->setComment($message)
            ->setEntityName('order')
            ->setOrder($order);

        $history->save();
    }
}
