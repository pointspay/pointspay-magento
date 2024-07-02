<?php

namespace Pointspay\Pointspay\Test\Model\Method;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Gateway\Command\CommandManager;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Config\ValueHandlerPool;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Request\BuilderComposite;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Operations\SaleOperation;
use Magento\Sales\Model\Order\Payment\Processor;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Config\ConfigFactory;
use Pointspay\Pointspay\Gateway\Config\ConfigValueHandler;
use Pointspay\Pointspay\Gateway\Http\Client\TransactionRefund;
use Pointspay\Pointspay\Gateway\Http\TransferFactory;
use Pointspay\Pointspay\Model\Method\Adapter;
use Pointspay\Pointspay\Service\Api\Refund\Refund;
use Pointspay\Pointspay\Service\Refund\Service;
use Pointspay\Pointspay\Test\MageObjectManager;
use Pointspay\Pointspay\Test\Service\Logger\LoggerTest\HandlerTest;

/**
 * @codingStandardsIgnoreFile
 */
class AdapterTest extends TestCase
{

    const TRANSACTION_ID = 'ewr34fM49V0';

    /**
     * @var Manager|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var PriceCurrency|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var Currency|MockObject
     */
    protected $currencyMock;

    /**
     * @var MockObject
     */
    protected $transactionCollectionFactory;

    /**
     * @var CreditmemoFactory|MockObject
     */
    protected $creditmemoFactoryMock;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditMemoMock;

    /**
     * @var Repository|MockObject
     */
    protected $transactionRepositoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $transactionManagerMock;

    /**
     * @var BuilderInterface|MockObject
     */
    protected $transactionBuilderMock;

    /**
     * @var Processor|MockObject
     */
    protected $paymentProcessor;

    /**
     * @var OrderRepository|MockObject
     */
    protected $orderRepository;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var OrderStateResolverInterface|MockObject
     */
    private $orderStateResolver;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Data|MockObject
     */
    private $helper;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var AbstractMethod|MockObject
     */
    private $paymentMethod;

    /**
     * @var Invoice|MockObject
     */
    private $invoice;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var CreditmemoManagementInterface|MockObject
     */
    private $creditmemoManagerMock;

    /**
     * @var SaleOperation|MockObject
     */
    private $saleOperation;

    /**
     * @var \Magento\Framework\App\ObjectManager|(\Magento\Framework\App\ObjectManager&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Framework\App\ObjectManager&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @see \Magento\Sales\Test\Unit\Model\Order\PaymentTest::testRefund
     */
    public function testRefund(): void
    {
        $amount = 204.04;
        $this->creditMemoMock->expects(static::once())
            ->method('getBaseGrandTotal')
            ->willReturn($amount);
        $this->creditMemoMock->expects(static::once())
            ->method('getGrandTotal')
            ->willReturn($amount);
        $this->creditMemoMock->expects(static::once())
            ->method('getDoTransaction')
            ->willReturn(true);

        $this->mockInvoice(self::TRANSACTION_ID, 0);
        $this->creditMemoMock->expects(static::once())
            ->method('getInvoice')
            ->willReturn($this->invoice);
        $this->creditMemoMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->order);

        $captureTranId = self::TRANSACTION_ID . '-' . Transaction::TYPE_CAPTURE;
        $captureTransaction = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTxnId'])
            ->getMock();

        $refundTranId = $captureTranId . '-' . Transaction::TYPE_REFUND;
        $this->transactionManagerMock->expects(static::once())
            ->method('generateTransactionId')
            ->willReturn($refundTranId);
        $captureTransaction->expects(static::once())
            ->method('getTxnId')
            ->willReturn($captureTranId);
        $this->transactionRepositoryMock->expects(static::once())
            ->method('getByTransactionId')
            ->willReturn($captureTransaction);

        $isOnline = true;
        $this->getTransactionBuilderMock([], $isOnline, Transaction::TYPE_REFUND, $refundTranId);

        $this->currencyMock->expects(static::once())
            ->method('formatTxt')
            ->willReturn($amount);
        $this->order->expects(static::once())
            ->method('getBaseCurrency')
            ->willReturn($this->currencyMock);

        $status = 'status';
        $message = 'We refunded ' . $amount . ' online. Transaction ID: "' . $refundTranId . '"';
        $this->orderStateResolver->expects($this->once())->method('getStateForOrder')
            ->with($this->order)
            ->willReturn(Order::STATE_CLOSED);
        $this->mockGetDefaultStatus(Order::STATE_CLOSED, $status, ['first, second']);
        $this->assertOrderUpdated(Order::STATE_PROCESSING, $status, $message);

        static::assertSame($this->payment, $this->payment->refund($this->creditMemoMock));
        static::assertEquals($amount, $this->payment->getData('amount_refunded'));
        static::assertEquals($amount, $this->payment->getData('base_amount_refunded_online'));
        static::assertEquals($amount, $this->payment->getData('base_amount_refunded'));
    }

    /**
     * @param string|null $transactionId
     * @param int $countCall
     *
     * @return void
     */
    private function mockInvoice(?string $transactionId, int $countCall = 1): void
    {
        $this->invoice->method('getTransactionId')
            ->willReturn($transactionId);
        $this->invoice->method('load')
            ->with($transactionId);
        $this->invoice->method('getId')
            ->willReturn($transactionId);
        $this->order->expects(self::exactly($countCall))
            ->method('getInvoiceCollection')
            ->willReturn([$this->invoice]);
    }

    /**
     * @param array $additionalInformation
     * @param bool $failSafe
     * @param mixed $transactionType
     * @param mixed $transactionId
     *
     * @return void
     */
    protected function getTransactionBuilderMock(
        array $additionalInformation,
        bool $failSafe,
        $transactionType,
        $transactionId = false
    ): void {
        if (!$transactionId) {
            $transactionId = $this->transactionId;
        }
        $this->transactionBuilderMock->expects($this->once())
            ->method('setPayment')
            ->with($this->payment)
            ->willReturnSelf();
        $this->transactionBuilderMock->expects($this->once())
            ->method('setOrder')
            ->with($this->order)
            ->willReturnSelf();
        $this->transactionBuilderMock->expects($this->once())
            ->method('setTransactionId')
            ->with($transactionId)
            ->willReturnSelf();
        $this->transactionBuilderMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with($additionalInformation)
            ->willReturnSelf();
        $this->transactionBuilderMock->expects($this->once())
            ->method('setFailSafe')
            ->with($failSafe)
            ->willReturnSelf();
        $transaction = $this->getTransactionMock($transactionId);
        $this->transactionBuilderMock->expects($this->once())
            ->method('build')
            ->with($transactionType)
            ->willReturn($transaction);
    }

    /**
     * @param string $transactionId
     * @return MockObject
     */
    protected function getTransactionMock(string $transactionId): MockObject
    {
        $transaction = $this->getMockBuilder(Transaction::class)
            ->addMethods(['loadByTxnId'])
            ->onlyMethods(
                [
                    'getId',
                    'setOrderId',
                    'setPaymentId',
                    'setTxnId',
                    'getTransactionId',
                    'setTxnType',
                    'isFailsafe',
                    'getTxnId',
                    'getHtmlTxnId',
                    'getTxnType'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $transaction->expects($this->any())->method('getId')->willReturn($transactionId);
        $transaction->expects($this->any())->method('getTxnId')->willReturn($transactionId);
        $transaction->expects($this->any())->method('getHtmlTxnId')->willReturn($transactionId);
        return $transaction;
    }

    /**
     * @param string $state
     * @param mixed $status
     * @param array $allStatuses
     */
    protected function mockGetDefaultStatus(string $state, $status, array $allStatuses = []): void
    {
        /** @var Config|MockObject $orderConfigMock */
        $orderConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStateStatuses', 'getStateDefaultStatus'])
            ->getMock();

        if (!empty($allStatuses)) {
            $orderConfigMock->expects($this->any())
                ->method('getStateStatuses')
                ->with($state)
                ->willReturn($allStatuses);
        }

        $orderConfigMock->expects($this->any())
            ->method('getStateDefaultStatus')
            ->with($state)
            ->willReturn($status);

        $this->order->expects($this->any())
            ->method('getConfig')
            ->willReturn($orderConfigMock);
    }

    /**
     * @param string $state
     * @param mixed $status
     * @param mixed $message
     * @param bool|null $isCustomerNotified
     */
    protected function assertOrderUpdated(
        string $state,
        $status = null,
        $message = null,
        bool $isCustomerNotified = null
    ): void {
        $this->order->expects($this->any())
            ->method('setState')
            ->with($state)
            ->willReturnSelf();
        $this->order->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();

        $statusHistory = $this->getMockForAbstractClass(
            OrderStatusHistoryInterface::class
        );
        $this->order->expects($this->any())
            ->method('addStatusHistoryComment')
            ->with($message)
            ->willReturn($statusHistory);
        $this->order->expects($this->any())
            ->method('setIsCustomerNotified')
            ->with($isCustomerNotified)
            ->willReturn($statusHistory);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.TooManyFields)
     */
    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleOperation = $this->getMockBuilder(SaleOperation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->atLeastOnce())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethodInstance'])
            ->getMock();

        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrency::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['format'])
            ->getMock();
        $this->currencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['formatTxt'])
            ->getMock();
        $transaction = Repository::class;
        $this->transactionRepositoryMock = $this->getMockBuilder($transaction)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'getByTransactionType', 'getByTransactionId'])
            ->getMock();
        $this->paymentProcessor = $this->createMock(Processor::class);
        $this->orderRepository = $this->createPartialMock(OrderRepository::class, ['get']);

        $this->priceCurrencyMock->expects($this->any())
            ->method('format')
            ->willReturnCallback(
                function ($value) {
                    return $value;
                }
            );

        // @codingStandardsIgnoreStart
        // phpcs:ignore Magento2.PHP.AutogeneratedClassNotInConstructor
        $this->objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);

        // phpcs:ignore Magento2.PHP.AutogeneratedClassNotInConstructor
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);
        // phpcs:ignore Magento2.PHP.AutogeneratedClassNotInConstructor
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        // @codingStandardsIgnoreEnd

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->with('payment/pointspay_required_settings/can_refund', 'store', 0)
            ->willReturn(true);
        //            ->withConsecutive([
        //                'payment/pointspay_required_settings/can_refund',
        //                'store',
        //                0
        //            ])
        //            ->willReturnOnConsecutiveCalls(
        //                true
        //            );
        $configInterface = $this->objectManagerHelper->getObject(
            \Pointspay\Pointspay\Gateway\Config\Config::class,
            [
                'scopeConfig' => $scopeConfig,
                'methodCode' => 'pointspay_required_settings'
            ]
        );

        $configFactory = $this->createMock(ConfigFactory::class);
        $valueHandlerPool = $this->createMock(ValueHandlerPool::class);
        $defaultConfigValueHandler = $this->objectManagerHelper->getObject(
            ConfigValueHandler::class,
            ['configInterface' => $configInterface]
        );
        $configFactory->expects($this->any())
            ->method('create')
            ->willReturn($configInterface);
        $valueHandlerPool->expects($this->any())
            ->method('get')
            ->willReturn(
                $defaultConfigValueHandler
            );

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getConfig',
                    'setState',
                    'setStatus',
                    'getStoreId',
                    'getBaseGrandTotal',
                    'getBaseCurrency',
                    'getBaseCurrencyCode',
                    'getTotalDue',
                    'getBaseTotalDue',
                    'getInvoiceCollection',
                    'addRelatedObject',
                    'getState',
                    'getStatus',
                    'addStatusHistoryComment',
                    'registerCancellation',
                    'getCustomerNote',
                    'prepareInvoice',
                    'getPaymentsCollection'
                ]
            )->addMethods(['setIsCustomerNotified'])
            ->getMock();

        $paymentDataObjectFactory = $this->createMock(PaymentDataObjectFactory::class);
        $order = $this->objectManagerHelper->getObject(
            OrderAdapter::class
        );
        $paymentMethod = $this->objectManagerHelper->getObject(
            Payment::class
        );
        $paymentObject = $this->objectManagerHelper->getObject(
            PaymentDataObject::class,
            [
                'order' => $order,
                'payment' => $paymentMethod
            ]
        );
        $paymentDataObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($paymentObject);

        $commandPool = $this->createMock(CommandPool::class);

        $realObjectManager = new MageObjectManager();

        $requestBuilder = $realObjectManager->objectManager->create(
            BuilderComposite::class,
            [
                'builders' => [
                    'private_key' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\PrivateKeyDataBuilder',
                    'public_key' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\PublicKeyDataBuilder',
                    'nonce' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\NonceDataBuilder',
                    'consumer_key' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\ConsumerKeyDataBuilder',
                    'pointspay_certificate' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\PointspayCertificateDataBuilder',
                    'timestamp' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\TimestampDataBuilder',
                    'amount' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\AmountDataBuilder',
                    'payment_id' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\PaymentId',
                    'refund_reason' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\RefundReason',
                    'payment_code' => '\Pointspay\Pointspay\Test\Model\Method\FakeBuilder\PaymentCodeDataBuilder',
                    'dynamic_urls' => 'Pointspay\Pointspay\Test\Model\Method\FakeBuilder\AdditionalData\DynamicUrlsDataBuilder'
                ]
            ]
        );
        $storeManager = $this->createMock(StoreManager::class);
        $config = $this->createMock(\Pointspay\Pointspay\Helper\Config::class);
        $testHandler = new HandlerTest();
        $logger = new FakeLogger($storeManager, $config, 'test', [$testHandler], []);
        // @codingStandardsIgnoreStart
        $checkoutFactory = $realObjectManager->objectManager->create(FakeCheckoutFactoryIgnorePattern::class);
        // @codingStandardsIgnoreEnd
        $refundCheckoutServiceForTransactionRefund = $realObjectManager->objectManager->create(
            Service::class,
            [
                'logger' => $logger,
                'checkoutFactory' => $checkoutFactory
            ]
        );

        $client = $realObjectManager->objectManager->create(
            TransactionRefund::class,
            [
                'checkoutService' => $refundCheckoutServiceForTransactionRefund
            ]
        );
        $transferFactory = $realObjectManager->objectManager->create(TransferFactory::class);
        //if you want to test with handler and validator - please provide proper classes in GatewayCommand constructor
        $refundCommand = $this->objectManagerHelper->getObject(
            GatewayCommand::class,
            [
                'requestBuilder' => $requestBuilder,
                'client' => $client,
                'transferFactory' => $transferFactory,
                'validator' => null,
                'handler' => null
            ]
        );
        $commandPool->expects($this->any())
            ->method('get')
            ->with('refund')
            ->willReturn($refundCommand);
        $commandExecutor = $this->objectManagerHelper->getObject(
            CommandManager::class,
            [
                'paymentDataObjectFactory' => $paymentDataObjectFactory,
                'commandPool' => $commandPool
            ]
        );
        $this->paymentMethod = $this->objectManagerHelper->getObject(
            Adapter::class,
            [
                'valueHandlerPool' => $valueHandlerPool,
                'configFactory' => $configFactory,
                'code' => 'pointspay_required_settings',
                'paymentDataObjectFactory' => $paymentDataObjectFactory,
                'commandExecutor' => $commandExecutor,
            ]
        );

        $this->invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getTransactionId',
                    'load',
                    'getId',
                    'pay',
                    'cancel',
                    'getGrandTotal',
                    'getBaseGrandTotal',
                    'getShippingAmount',
                    'getBaseShippingAmount',
                    'getBaseTotalRefunded',
                    'getItemsCollection',
                    'getOrder',
                    'register',
                    'capture'
                ]
            )->getMock();
        $this->helper->method('getMethodInstance')
            ->willReturn($this->paymentMethod);

        $this->transactionCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->onlyMethods(['create'])
            ->getMock();
        $this->creditmemoFactoryMock = $this->createMock(CreditmemoFactory::class);
        $this->transactionManagerMock = $this->createMock(
            Transaction\Manager::class
        );
        $this->transactionBuilderMock = $this->createMock(
            Builder::class
        );
        $this->orderStateResolver = $this->getMockBuilder(OrderStateResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditMemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getItemsCollection',
                    'getItems',
                    'addComment',
                    'save',
                    'getGrandTotal',
                    'getBaseGrandTotal',
                    'getInvoice',
                    'getOrder'
                ]
            )->addMethods(
                [
                    'setPaymentRefundDisallowed',
                    'setAutomaticallyCreated',
                    'register',
                    'getDoTransaction',
                    'getPaymentRefundDisallowed'
                ]
            )->getMock();

        $this->creditmemoManagerMock = $this->getMockBuilder(CreditmemoManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->payment = $this->initPayment();
        $helper = new ObjectManager($this);
        $helper->setBackwardCompatibleProperty($this->payment, 'orderStateResolver', $this->orderStateResolver);
        $this->payment->setMethod('any');
        $this->payment->setOrder($this->order);
        $this->transactionId = self::TRANSACTION_ID;
    }

    protected function initPayment(): object
    {
        return (new ObjectManager($this))->getObject(
            Payment::class,
            [
                'context' => $this->context,
                'creditmemoFactory' => $this->creditmemoFactoryMock,
                'paymentData' => $this->helper,
                'priceCurrency' => $this->priceCurrencyMock,
                'transactionRepository' => $this->transactionRepositoryMock,
                'transactionManager' => $this->transactionManagerMock,
                'transactionBuilder' => $this->transactionBuilderMock,
                'paymentProcessor' => $this->paymentProcessor,
                'orderRepository' => $this->orderRepository,
                'creditmemoManager' => $this->creditmemoManagerMock,
                'saleOperation' => $this->saleOperation
            ]
        );
    }
}
