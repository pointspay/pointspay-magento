<?php
namespace Pointspay\Pointspay\Test\Model;

use Exception;
use Magento\Directory\Model\Currency;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Ipn;
use Pointspay\Pointspay\Service\Checkout\Service;
use Pointspay\Pointspay\Service\Logger\Logger;
use Psr\Log\LoggerInterface;

class IpnTest extends TestCase
{
    private $logger;
    private $orderFactory;
    private $orderHistoryFactory;
    private $service;
    private $transactionBuilder;
    private $ipn;
    private $orderSender;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->orderSender = $this->createMock(OrderSender::class);
        $this->orderHistoryFactory = $this->createMock(HistoryFactory::class);
        $this->service = $this->createMock(Service::class);
        $this->transactionBuilder = $this->createMock(Builder::class);

        $this->ipn = new Ipn(
            $this->logger,
            $this->orderFactory,
            $this->orderSender,
            $this->orderHistoryFactory,
            $this->service,
            $this->transactionBuilder,
            []
        );
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessIpnRequestWithValidData()
    {
        $ipnData = [
            'order_id' => '100000001',
            'payment_id' => '123456789',
            'status' => 'SUCCESS'
        ];

        $orderMock = $this->createMock(Order::class);
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($ipnData['order_id'])
            ->willReturnSelf();
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->any())
            ->method('setTransactionId')
            ->with($ipnData['payment_id'])
            ->willReturnSelf();
        $paymentMock->expects($this->any())
            ->method('setLastTransId')
            ->with($ipnData['payment_id'])
            ->willReturnSelf();
        $additionalInfoData = [
            'payment_status' => $ipnData['status'],
            'payment_id' => $ipnData['payment_id']
        ];
        $paymentMock->expects($this->any())
            ->method('getAdditionalInformation')
            ->willReturn($additionalInfoData);
        $additionalInfo =  [Transaction::RAW_DETAILS => (array)$paymentMock->getAdditionalInformation()];
        $paymentMock->expects($this->any())
            ->method('setAdditionalInformation')
            ->with($additionalInfo)
            ->willReturnSelf();

        $orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $currencyMock = $this->createMock(Currency::class);
        $orderMock->expects($this->any())->method('getGrandTotal')->willReturn(100);

        $currencyMock->expects($this->any())->method('formatTxt')->with(100)->willReturn('100');
        $currencyMock->expects($this->any())->method('getCurrencyCode')->willReturn('USD');

        $orderMock->expects($this->any())->method('getBaseCurrency')->willReturn($currencyMock);

        $this->transactionBuilder = $this->createMock(Builder::class);


        $this->transactionBuilder->expects($this->once())
            ->method('setPayment')
            ->with($paymentMock)
            ->willReturnSelf();
        $this->transactionBuilder->expects($this->once())
            ->method('setOrder')
            ->with($orderMock)
            ->willReturnSelf();
        $this->transactionBuilder->expects($this->once())
            ->method('setTransactionId')
            ->with($ipnData['payment_id'])
            ->willReturnSelf();
        $this->transactionBuilder->expects($this->once())
            ->method('setAdditionalInformation')
            ->with($additionalInfo)
            ->willReturnSelf();
        $this->transactionBuilder->expects($this->once())
            ->method('setFailSafe')
            ->with(true)
            ->willReturnSelf();
        $transaction = $this->createMock(Transaction::class);

        $this->transactionBuilder->expects($this->once())
            ->method('build')
            ->with(Transaction::TYPE_CAPTURE)
            ->willReturn($transaction);
        $invoiceMock = $this->createMock(Invoice::class);
        $orderMock->expects($this->any())
            ->method('prepareInvoice')
            ->willReturn($invoiceMock);
        $orderMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();

        $orderMockFromInvoice = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsInProcess'])
            ->getMock();



        $invoiceMock->expects($this->any())->method('getOrder')->willReturn($orderMockFromInvoice);
        $invoiceMock->expects($this->any())->method('register')->willReturnSelf();
        $invoiceMock->expects($this->any())->method('pay')->willReturnSelf();
        $invoiceMock->expects($this->any())->method('save')->willReturnSelf();

        $this->orderHistoryFactory = $this->createMock(HistoryFactory::class);
        $orderHistoryMock = $this->createMock(Order\Status\History::class);
        $orderHistoryMock->expects($this->any())
            ->method('setIsCustomerNotified')
            ->willReturnSelf();
        $orderMock->expects($this->any())
            ->method('addStatusHistoryComment')
            ->willReturn($orderHistoryMock);
        $orderHistoryMock->expects($this->any())
            ->method('setComment')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('setEntityName')
            ->with('order')
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('setOrder')
            ->with($orderMock)
            ->willReturnSelf();
        $orderHistoryMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();
        $this->orderHistoryFactory->expects($this->once())
            ->method('create')
            ->willReturn($orderHistoryMock);
        $this->ipn = new Ipn(
            $this->logger,
            $this->orderFactory,
            $this->orderSender,
            $this->orderHistoryFactory,
            $this->service,
            $this->transactionBuilder,
            []
        );
        $this->ipn->processIpnRequest($ipnData);
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessIpnRequestWithInvalidOrderId()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The "100000002" order ID is incorrect. Verify the ID and try again.');


        $ipnData = [
            'order_id' => '100000002',
            'payment_id' => '123456789',
            'status' => 'SUCCESS'
        ];

        $orderMock = $this->createMock(Order::class);
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($ipnData['order_id'])
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->ipn->processIpnRequest($ipnData);
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessIpnRequestWithInvalidStatus()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The 'FAILURE' payment status couldn't be handled. Order IncrementId: '100000001'.");

        $ipnData = [
            'order_id' => '100000001',
            'payment_id' => '123456789',
            'status' => 'FAILURE'
        ];

        $orderMock = $this->createMock(Order::class);
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($ipnData['order_id'])
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->ipn->processIpnRequest($ipnData);
    }
}
