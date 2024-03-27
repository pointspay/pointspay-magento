<?php

namespace Pointspay\Pointspay\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Pointspay\Pointspay\Service\Checkout\Service;
use Psr\Log\LoggerInterface;

class Ipn implements \Pointspay\Pointspay\Api\IpnInterface
{

    const PAYMENTSTATUS_COMPLETED = 'SUCCESS';
    /**
     * @var OrderFactory
     */
    private $_orderFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var array
     */
    private $_ipnRequest;

    /**
     * @var HistoryFactory
     */
    private $_orderHistoryFactory;

    /**
     * @var BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @param LoggerInterface $logger
     * @param OrderFactory $orderFactory
     * @param OrderSender $orderSender
     * @param CreditmemoSender $creditmemoSender
     * @param HistoryFactory $orderHistoryFactory
     * @param Service $service
     * @param BuilderInterface $transactionBuilder
     * @param array $data
     */

    public function __construct(
        LoggerInterface     $logger,
        OrderFactory        $orderFactory,
        OrderSender         $orderSender,
        HistoryFactory      $orderHistoryFactory,
        Service             $service,
        BuilderInterface $transactionBuilder,
        array               $data = []
    ) {
        $this->_orderFactory        = $orderFactory;
        $this->_orderHistoryFactory = $orderHistoryFactory;
        $this->service              = $service;
        $this->logger               = $logger;
        $this->orderSender          = $orderSender;
        $this->_ipnRequest          = $data;
        $this->transactionBuilder   = $transactionBuilder;
    }

    /**
     * @param array $ipnData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processIpnRequest($ipnData):void
    {
        $this->_ipnRequest = $ipnData;

        $this->service->logResponse('IPN response handling', $this->_ipnRequest);

        try {
            $this->_getOrder();
            $this->_registerTransaction();
        } catch (\Exception $e) {
            $this->logger->addCritical(__METHOD__ . " Failed to init IPN", $this->_ipnRequest);
            $this->service->logException($e->getMessage(), $this->_ipnRequest);
            throw $e;
        }
    }

    /**
     * Process regular IPN notifications
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _registerTransaction()
    {
        // Handle payment_status
        $paymentStatus = $this->getRequestData(self::STATUS);
        switch ($paymentStatus) {
            case self::PAYMENTSTATUS_COMPLETED:
                $this->_registerPaymentCapture(true);
                break;
            default:
                $message ="The '{$paymentStatus}' payment status couldn't be handled. Order IncrementId: '{$this->getRequestData(self::ORDER_ID)}'.";
                $this->service->logException($message, $this->_ipnRequest);
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception($message);
        }
    }

    /**
     * Process completed payment (either full or partial)
     *
     * @param bool $skipFraudDetection
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _registerPaymentCapture(bool $skipFraudDetection = false)
    {
        $payment = $this->createTransaction();
        if ($this->_order->getState() === Order::STATE_PENDING_PAYMENT) {
            $this->_order->setState(Order::STATE_PROCESSING);
        }

        $this->createInvoice($payment);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface
     * @throws \Exception
     */
    private function createTransaction()
    {
        $payment = $this->_order->getPayment();

        $trans_id = $this->getRequestData(self::PAYMENT_ID);
        $payment->setLastTransId($trans_id);
        $payment->setTransactionId($trans_id);

        $formatedPrice = $this->_order->getBaseCurrency()->formatTxt(
            $this->_order->getGrandTotal()
        );

        $message = __('The Captured amount is %1.', $formatedPrice);
        $trans = $this->transactionBuilder;
        $transaction = $trans->setPayment($payment)
            ->setOrder($this->_order)
            ->setTransactionId($trans_id)
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$payment->getAdditionalInformation()]
            )
            ->setFailSafe(true)
            //build method creates the transaction and returns the object
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

        $payment->addTransactionCommentsToOrder(
            $transaction,
            $message
        );
        $payment->setParentTransactionId(null);

        $payment->save();
        $this->_order->save();
        $transaction->save();

        return $payment;
    }

    /**
     * @param $payment
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createInvoice($payment)
    {
        $paymentTransactionId = $payment->getTransactionId();
        $invoice = $this->_order->prepareInvoice();
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->setTransactionId($paymentTransactionId);
        $invoice->register()
            ->pay()
            ->save();

        $this->_order->save();

        $message = __(
            'Invoiced amount of %1 Transaction ID: %2',
            $this->_order->formatPriceTxt($payment->getAmountOrdered()),
            $paymentTransactionId
        );
        $this->_addHistoryComment($this->_order, $message);

        if (!$this->_order->getEmailSent()) {
            $this->orderSender->send($this->_order);
            $history =    $this->_order->addStatusHistoryComment(
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
    private function _addHistoryComment($order, $message)
    {
        $history = $this->_orderHistoryFactory->create()
            ->setComment($message)
            ->setEntityName('order')
            ->setOrder($order);

        $history->save();
    }

    /**
     * IPN request data getter
     *
     * @param string $key
     * @return array|string
     */
    public function getRequestData($key = null)
    {
        if (null === $key) {
            return $this->_ipnRequest;
        }
        return $this->_ipnRequest[$key] ?? null;
    }

    /**
     * Load order
     *
     * @return Order
     * @throws \Exception
     */
    protected function _getOrder()
    {
        $incrementId = $this->getRequestData(self::ORDER_ID);
        $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        if (!$this->_order->getId()) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            $message = sprintf('The "%s" order ID is incorrect. Verify the ID and try again.', $incrementId);
            $this->service->logException($message, $this->_ipnRequest);
            throw new \Exception($message);
        }
        return $this->_order;
    }
}
