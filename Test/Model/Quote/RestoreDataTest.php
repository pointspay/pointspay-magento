<?php

namespace Pointspay\Pointspay\Test\Model\Quote;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\Manager;
use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Quote\RestoreData;
use Pointspay\Pointspay\Service\Logger\Logger;

class RestoreDataTest extends TestCase
{
    protected $restoreData;

    /**
     * @var \Magento\Customer\Model\Session|(\Magento\Customer\Model\Session&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Customer\Model\Session&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session|(\Magento\Checkout\Model\Session&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Checkout\Model\Session&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory|(\Magento\Sales\Model\OrderFactory&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Sales\Model\OrderFactory&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\Session\Generic|(\Magento\Framework\Session\Generic&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Framework\Session\Generic&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $genericSession;

    /**
     * @var \Magento\Quote\Model\QuoteRepository|(\Magento\Quote\Model\QuoteRepository&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Quote\Model\QuoteRepository&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cartRepository;

    /**
     * @var \Magento\Sales\Model\Order|(\Magento\Sales\Model\Order&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Sales\Model\Order&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderModel;

    /**
     * @var \Magento\Framework\Message\Manager|(\Magento\Framework\Message\Manager&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Framework\Message\Manager&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManager;

    /**
     * @var (\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject|\Pointspay\Pointspay\Service\Logger\Logger|(\Pointspay\Pointspay\Service\Logger\Logger&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Pointspay\Pointspay\Service\Logger\Logger&\PHPUnit\Framework\MockObject\MockObject)
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\ObjectManager|(\Magento\Framework\App\ObjectManager&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Framework\App\ObjectManager&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    private $arrayOfProductItems = [];

    /**
     * @var object
     */
    private $orderResourceMock;

    public function testRestoreCartFromOrderWithAbsentQuote()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManagerHelper->getObject(Order::class);
        $this->expectException(NoSuchEntityException::class);
        $this->cartRepository->expects($this->once())
            ->method('get')
            ->willThrowException(new NoSuchEntityException());
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->restoreCart($order);
        $this->assertFalse($result);
    }
    public function testRestoreCartFromOrderWithNonActiveQuote()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManagerHelper->getObject(Order::class);
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->any())
            ->method('getIsActive')
            ->willReturn(true);
        $this->cartRepository->expects($this->once())
            ->method('get')
            ->willReturn($quoteMock);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->restoreCart($order);
        $this->assertFalse($result);
    }
    public function testRestoreCartFromOrderUnexpectedPoint()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManagerHelper->getObject(Order::class);
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->any())
            ->method('getIsActive')
            ->willReturn(false);
        $this->cartRepository->expects($this->once())
            ->method('get')
            ->willReturn($quoteMock);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->checkoutSession->expects($this->any())
            ->method('getLastRealOrderId')
            ->willReturn(100);
        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->restoreCart($order);
        $this->assertFalse($result);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testRestoreCartFromOrderWithSuccessQuoteRestoring()
    {
        $order = $this->getOrderForQuoteRestoring();
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->restoreCart($order);
        $this->assertTrue($result);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrderForQuoteRestoring(): Order
    {
        $orderHistoryFactory = $this->createMock(HistoryFactory::class);
        $orderHistoryMock = $this->createMock(History::class);
        $orderHistoryMock->expects($this->any())
            ->method('setStatus')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('setComment')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('setEntityName')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('setIsVisibleOnFront')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $orderHistoryFactory->expects($this->any())
            ->method('create')
            ->willReturn($orderHistoryMock);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManagerHelper->getObject(
            Order::class,
            [
                'data' => [
                    'quote_id' => 1,
                    'real_order_id' => 1,
                    'items' => $this->arrayOfProductItems,
                    'id' => 1
                ],
                'orderHistoryFactory' => $orderHistoryFactory
            ]
        );
        $quoteMock = $this->createMock(Quote::class);
        $this->cartRepository->expects($this->once())
            ->method('get')->willReturn($quoteMock);
        $this->checkoutSession->expects($this->any())
            ->method('getLastRealOrderId')
            ->willReturn(1);

        $this->checkoutSession->expects($this->any())
            ->method('restoreQuote')
            ->willReturn(true);

        $this->orderResourceMock->expects($this->any())
            ->method('save')
            ->willReturn($order);
        return $order;
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testRestoreCartFromOrderWithFailingQuoteRestoring()
    {
        $orderHistoryFactory = $this->createMock(HistoryFactory::class);
        $orderHistoryMock = $this->createMock(History::class);
        $orderHistoryMock->expects($this->any())
            ->method('setStatus')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('setComment')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('setEntityName')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('setIsVisibleOnFront')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $orderHistoryFactory->expects($this->any())
            ->method('create')
            ->willReturn($orderHistoryMock);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManagerHelper->getObject(
            Order::class,
            [
                'data' => [
                    'quote_id' => 1,
                    'real_order_id' => 1,
                    'items' => $this->arrayOfProductItems
                ],
                'orderHistoryFactory' => $orderHistoryFactory
            ]
        );
        $quoteMock = $this->createMock(Quote::class);
        $this->cartRepository->expects($this->once())
            ->method('get')->willReturn($quoteMock);
        $this->checkoutSession->expects($this->any())
            ->method('getLastRealOrderId')
            ->willReturn(1);

        $this->checkoutSession->expects($this->any())
            ->method('restoreQuote')
            ->willReturn(false);

        $this->orderResourceMock->expects($this->any())
            ->method('save')
            ->willReturn($order);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->restoreCart($order);
        $this->assertTrue($result);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCancelOrder()
    {
        $orderData = ['order_id' => 1];
        $order = $this->orderModel = $this->getOrderForQuoteRestoring();


        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'unsLastQuoteId',
                'unsLastSuccessQuoteId',
                'unsLastOrderId',
                'unsLastRealOrderId',
                'getLastRealOrderId',
                'setLastRealOrderId'
            ])
            ->getMock();
        $this->checkoutSession->expects($this->any())
            ->method('unsLastQuoteId')
            ->willReturnSelf();
        $this->checkoutSession->expects($this->any())
            ->method('unsLastSuccessQuoteId')
            ->willReturnSelf();
        $this->checkoutSession->expects($this->any())
            ->method('unsLastOrderId')
            ->willReturnSelf();
        $this->checkoutSession->expects($this->any())
            ->method('unsLastRealOrderId')
            ->willReturnSelf();
        $this->checkoutSession->expects($this->any())
            ->method('setLastRealOrderId')
            ->willReturnSelf();
        $this->orderResourceMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->orderFactory->expects($this->any())
            ->method('create')
            ->willReturn($order);

        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->cancelOrder($orderData, 'type');
        $this->assertTrue($result);

    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCancelOrderBypassAllCode()
    {
        $orderData = ['order_id' => '1'];
        $this->orderModel = $this->createMock(Order::class);
        $this->orderModel->expects($this->any())
            ->method('loadByAttribute')
            ->willReturn(null);
        $this->orderModel->expects($this->any())
            ->method('getId')
            ->willReturn(null);
        $this->orderResourceMock->expects($this->any())
            ->method('load')
            ->willReturn($this->orderModel);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->orderFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->orderModel);
        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->cancelOrder($orderData, 'type');
        $this->assertFalse($result);

    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCancelOrderLocalizedException()
    {
        $orderData = ['order_id' => '1'];
        $this->orderModel = $this->createMock(Order::class);
        $this->orderResourceMock->expects($this->any())
            ->method('load')
            ->willReturn($this->orderModel);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->orderFactory->expects($this->any())
            ->method('create')
            ->willThrowException(new LocalizedException(__('Exception')));
        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->cancelOrder($orderData, 'type');
        $this->assertFalse($result);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCancelOrderException()
    {
        $orderData = ['order_id' => '1'];
        $this->orderModel = $this->createMock(Order::class);
        $this->orderResourceMock->expects($this->any())
            ->method('load')
            ->willReturn($this->orderModel);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->orderFactory->expects($this->any())
            ->method('create')
            ->willThrowException(new Exception(__('Exception')));
        $this->restoreData = new RestoreData(
            $this->customerSession,
            $this->checkoutSession,
            $this->orderFactory,
            $this->genericSession,
            $this->cartRepository,
            $this->orderModel,
            $this->messageManager,
            $this->logger,
            $this->orderResourceMock
        );
        $result = $this->restoreData->cancelOrder($orderData, 'type');
        $this->assertFalse($result);
    }

    protected function setUp(): void
    {
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLastRealOrderId'])
            ->onlyMethods(['restoreQuote'])
            ->getMock();
        $this->genericSession = $this->createMock(Generic::class);
        $this->cartRepository = $this->createMock(QuoteRepository::class);
        $this->orderModel = $this->createMock(Order::class);
        $this->messageManager = $this->createMock(Manager::class);
        $this->logger = $this->createMock(Logger::class);
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->orderResourceMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order::class);

        ObjectManager::setInstance($this->objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $item1 = $this->objectManagerHelper->getObject(
            Item::class,
            [
                'data' => [
                    'qty_ordered' => 2,
                    'qty_invoiced' => 1
                ]
            ]
        );
        $item2 = $this->objectManagerHelper->getObject(
            Item::class,
            [
                'data' => [
                    'qty_ordered' => 2,
                    'qty_invoiced' => 1
                ]
            ]
        );
        //\Magento\Sales\Model\Order\Item
        $this->arrayOfProductItems = [
            $item1,
            $item2
        ];
    }

}
