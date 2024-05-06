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
use Pointspay\Pointspay\Api\InvoiceMutexInterface;
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
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessIpnRequestWithValidData()
    {
        $logger = $this->createMock(Logger::class);
        $orderFactory = $this->createMock(OrderFactory::class);
        $service = $this->createMock(Service::class);
        $invoiceMutex = $this->createMock(InvoiceMutexInterface::class);
        $paymentProcessor = $this->createMock(\Pointspay\Pointspay\Service\Api\Success\PaymentProcessor\Ipn::class);


        $ipnData = [
            'order_id' => '100000001',
            'payment_id' => '123456789',
            'status' => 'SUCCESS'
        ];

        $orderMock = $this->createMock(Order::class);
        $orderFactory->expects($this->once())
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

        $paymentProcessor->expects($this->once())
            ->method('processInvoice')
            ->with($orderMock, $ipnData)
            ->willReturn(true);
        $ipn = new Ipn(
            $logger,
            $orderFactory,
            $service,
            $paymentProcessor,
            $invoiceMutex
        );
        $ipn->processIpnRequest($ipnData);
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessIpnRequestWithInvalidOrderId()
    {
        $logger = $this->createMock(Logger::class);
        $orderFactory = $this->createMock(OrderFactory::class);
        $service = $this->createMock(Service::class);
        $paymentProcessor = $this->createMock(\Pointspay\Pointspay\Service\Api\Success\PaymentProcessor\Ipn::class);
        $this->expectException(Exception::class);
        $invoiceMutex = $this->createMock(InvoiceMutexInterface::class);
        $this->expectExceptionMessage('The "100000002" order ID is incorrect. Verify the ID and try again.');


        $ipnData = [
            'order_id' => '100000002',
            'payment_id' => '123456789',
            'status' => 'SUCCESS'
        ];

        $orderMock = $this->createMock(Order::class);
        $orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($ipnData['order_id'])
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $ipn = new Ipn(
            $logger,
            $orderFactory,
            $service,
            $paymentProcessor,
            $invoiceMutex
        );
        $ipn->processIpnRequest($ipnData);
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessIpnRequestWithInvalidStatus()
    {
        $logger = $this->createMock(Logger::class);
        $orderFactory = $this->createMock(OrderFactory::class);
        $service = $this->createMock(Service::class);
        $paymentProcessor = $this->createMock(\Pointspay\Pointspay\Service\Api\Success\PaymentProcessor\Ipn::class);
        $invoiceMutex = $this->createMock(InvoiceMutexInterface::class);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The 'FAILURE' payment status couldn't be handled. Order IncrementId: '100000001'.");

        $ipnData = [
            'order_id' => '100000001',
            'payment_id' => '123456789',
            'status' => 'FAILURE'
        ];

        $orderMock = $this->createMock(Order::class);
        $orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($ipnData['order_id'])
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $ipn = new Ipn(
            $logger,
            $orderFactory,
            $service,
            $paymentProcessor,
            $invoiceMutex
        );
        $ipn->processIpnRequest($ipnData);
    }
}
