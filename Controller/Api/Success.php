<?php

namespace Pointspay\Pointspay\Controller\Api;

use Closure;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Pointspay\Pointspay\Api\InvoiceMutexInterface;
use Pointspay\Pointspay\Api\IpnInterface;
use Pointspay\Pointspay\Model\Quote\RestoreData;
use Pointspay\Pointspay\Service\Api\Success\PaymentProcessor;
use Pointspay\Pointspay\Service\Checkout\Service;
use Psr\Log\LoggerInterface;

class Success extends AbstractApi
{

    /**
     * @var Order
     */
    protected $_orderManager;

    /**
     * @var checkoutSession
     */
    protected $_checkoutSession;

    /**
     * @var \Pointspay\Pointspay\Service\Api\Success\PaymentProcessor
     */
    private $paymentProcessor;

    /**
     * @var \Pointspay\Pointspay\Api\InvoiceMutexInterface
     */
    private $invoiceMutex;

    /**
     * @param Context $context
     * @param RestoreData $restoreData
     * @param LoggerInterface $logger
     * @param Service $service
     * @param Redirect $resultRedirectFactory
     * @param Order $orderManager
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        RestoreData $restoreData,
        LoggerInterface $logger,
        Service $service,
        Redirect $resultRedirectFactory,
        Order               $orderManager,
        CheckoutSession $checkoutSession,
        PaymentProcessor $paymentProcessor,
        InvoiceMutexInterface $invoiceMutex
    ) {
        parent::__construct($context, $restoreData, $logger, $service, $resultRedirectFactory);
        $this->_orderManager        = $orderManager;
        $this->_checkoutSession     = $checkoutSession;
        $this->paymentProcessor = $paymentProcessor;
        $this->invoiceMutex = $invoiceMutex;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $content = $this->getRequest()->getContent();
        $this->service->logPostData($content);

        if ($postData = $this->service->restorePostData($content)) {
            $lastRealOrderId = $postData['order_id'];
        } else {
            $lastRealOrderId = $this->_checkoutSession->getLastRealOrderId();
        }

        $order = $this->_orderManager->loadByAttribute("increment_id", $lastRealOrderId);

        if (!$order) {
            $this->_redirect("checkout/", [
                "_secure" => true
            ]);
            return;
        }

        $state = $order->getState();
        if ($state == Order::STATE_CANCELED) {
            $this->logger->addInfo(__METHOD__ . " transaction has failed or been cancelled. Restoring the cart.");
            $this->_restoreData->restoreCart($order);
            $this->_redirectToCartPageWithError("Payment failed.", [], 1);
            return;
        }
        $invoiceProcessingResult = $this->invoiceMutex->execute(
            $order->getIncrementId(),
            Closure::fromCallable([$this, 'processInvoice']),
            [$order, $postData]
        );
        if (!$invoiceProcessingResult){
            $this->logger->addInfo("Invoice creation during success page (Sale method) redirection process has skipped.");
        }
        $quoteId = $order->getQuoteId();
        $this->_checkoutSession->setLastQuoteId($quoteId);
        $this->_checkoutSession->setLastSuccessQuoteId($quoteId);
        $this->_checkoutSession->setLastOrderId($order->getId());
        $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->_checkoutSession->setLastOrderStatus($order->getStatus());
        $this->_redirect("checkout/onepage/success", [
                "_secure" => true
        ]);
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
