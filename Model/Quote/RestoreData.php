<?php

namespace Pointspay\Pointspay\Model\Quote;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Session\Generic;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Psr\Log\LoggerInterface;

class RestoreData
{

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var Order
     */
    protected $orderModel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Generic
     */
    private $_pointspaySession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    private $orderResource;

    /**
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     * @param OrderFactory $orderFactory
     * @param Generic $pointspaySession
     * @param CartRepositoryInterface $quoteRepository
     * @param Order $orderModel
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $customerSession,
        CheckoutSession $checkoutSession,
        OrderFactory $orderFactory,
        Generic $pointspaySession,
        CartRepositoryInterface $quoteRepository,
        Order $orderModel,
        MessageManagerInterface $messageManager,
        LoggerInterface $logger,
        OrderResource $orderResource
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_pointspaySession = $pointspaySession;
        $this->_quoteRepository = $quoteRepository;
        $this->orderModel = $orderModel;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->orderResource = $orderResource;
    }

    /**
     * @param array $orderData
     * @param string $type
     * @return bool
     */
    public function cancelOrder(array $orderData, string $type)
    {
        $result = false;
        try {
            $orderId = $orderData['order_id'];
            $orderModel = $this->_orderFactory->create();
            $this->orderResource->load($orderModel, $orderId, 'increment_id');
            /** @var Order $orderModel */
            if (!empty($orderModel) && $orderModel->getId()) {
                $this->getCheckoutSession()->clearHelperData();
                $this->getCheckoutSession()
                    ->unsLastQuoteId()
                    ->unsLastSuccessQuoteId()
                    ->unsLastOrderId()
                    ->unsLastRealOrderId();

                $this->getCheckoutSession()->setLastRealOrderId($orderId);
                $this->restoreCart($orderModel);
                $result = true;
            }

        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Payment return type -  ' . $type . '. Cancel Payment'));
        }
        return $result;

    }

    /**
     * Return checkout session object
     *
     * @return CheckoutSession
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Restores the cart
     *
     * @param Order $order
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function restoreCart(Order $order)
    {
        $quote = $this->_quoteRepository->get($order->getQuoteId());
        if ($quote->getIsActive() == 1) {
            // Quote is already restored. Nothing to do here.
            return false;
        }

        $orderId = $this->_checkoutSession->getLastRealOrderId();
        if ($order->getRealOrderId() == $orderId) {
            $this->logger->addInfo(
                __METHOD__ . " order id matches. LastReadOrderId:{$orderId} " .
                "OrderToCancel:{$order->getRealOrderId()}"
            );
            // restore the quote
            if ($this->_checkoutSession->restoreQuote()) {
                $this->logger->addInfo(__METHOD__ . " Quote has been restored.");
            } else {
                $this->logger->addError(__METHOD__ . " Failed to restore the quote.");
            }
            $order->setActionFlag(Order::ACTION_FLAG_CANCEL, true);
            $order = $order->registerCancellation(__('Payment cancelled.'));
            $this->orderResource->save($order);
            $order = $order->cancel();
            $this->orderResource->save($order);
            return true;
        } elseif ($order->getId()) {
            $this->logger->addWarning(__METHOD__ . " attempting to cancel the order which is not the last one. " .
                "LastRealOrderId:{$this->_checkoutSession->getLastRealOrderId()} " .
                "OrderToCancel:{$order->getRealOrderId()}. Silently cancelling.");
            $order->setActionFlag(Order::ACTION_FLAG_CANCEL, true);
            $order = $order->registerCancellation(__('Payment cancelled.'));
            $this->orderResource->save($order);
            $order = $order->cancel();
            $this->orderResource->save($order);
            return true;
        } else {
            $this->logger->addError(__METHOD__ . " Unexpected point where no order to restore or to cancel.");
            return false;
        }
    }
}
