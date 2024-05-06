<?php

namespace Pointspay\Pointspay\Model;

use Closure;
use Exception;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Pointspay\Pointspay\Api\InvoiceMutexInterface;
use Pointspay\Pointspay\Service\Api\Success\PaymentProcessor;
use Pointspay\Pointspay\Service\Checkout\Service;
use Psr\Log\LoggerInterface;

class Ipn implements \Pointspay\Pointspay\Api\IpnInterface
{

    const PAYMENTSTATUS_COMPLETED = 'SUCCESS';
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var \Pointspay\Pointspay\Service\Api\Success\PaymentProcessor
     */
    private $paymentProcessor;

    /**
     * @var \Pointspay\Pointspay\Api\InvoiceMutexInterface
     */
    private $invoiceMutex;

    /**
     * @param LoggerInterface $logger
     * @param OrderFactory $orderFactory
     * @param Service $service
     * @param \Pointspay\Pointspay\Service\Api\Success\PaymentProcessor\Ipn $paymentProcessor
     * @param \Pointspay\Pointspay\Api\InvoiceMutexInterface $invoiceMutex
     */

    public function __construct(
        LoggerInterface     $logger,
        OrderFactory        $orderFactory,
        Service             $service,
        PaymentProcessor\Ipn $paymentProcessor,
        InvoiceMutexInterface $invoiceMutex
    ) {
        $this->orderFactory = $orderFactory;
        $this->service              = $service;
        $this->logger               = $logger;
        $this->paymentProcessor = $paymentProcessor;
        $this->invoiceMutex = $invoiceMutex;
    }

    /**
     * @param array $gatewayData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processIpnRequest($gatewayData): void
    {
        $this->service->logResponse('IPN response handling', $gatewayData);
        try {
            $order = $this->getOrder($gatewayData);
            $invoiceProcessingResult = $this->invoiceMutex->execute(
                $order->getIncrementId(),
                Closure::fromCallable([$this, 'processInvoice']),
                [$order, $gatewayData]
            );
            if (!$invoiceProcessingResult) {
                $message = "The '{$gatewayData[Ipn::STATUS]}' payment status couldn't be handled. Order IncrementId: '{$gatewayData[Ipn::ORDER_ID]}'.";
                $message2 = "Invoice creation during success page (IPN method) redirection process has skipped.";
                $this->service->logException($message, $gatewayData);
                $this->service->logException($message2, $gatewayData);
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new Exception($message);
            }
        } catch (Exception $e) {
            $this->logger->addCritical(__METHOD__ . " Failed to init IPN", $gatewayData);
            $this->service->logException($e->getMessage(), $gatewayData);
            throw $e;
        }
    }


    /**
     * @param array $gatewayData
     * @return \Magento\Sales\Model\Order|null
     * @throws \Exception
     */
    public function getOrder(array $gatewayData)
    {
        $incrementId = $gatewayData[self::ORDER_ID];
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if (!$order->getId()) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            $message = sprintf('The "%s" order ID is incorrect. Verify the ID and try again.', $incrementId);
            $this->service->logException($message, $gatewayData);
            throw new Exception($message);
        }
        return $order;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param array $postData
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processInvoice(Order $order, array $postData)
    {
        return $this->paymentProcessor->processInvoice($order, $postData);
    }
}
