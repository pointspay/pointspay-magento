<?php

namespace Pointspay\Pointspay\Test\Service\Api\Success;

use Magento\Directory\Model\Currency;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Api\Success\PaymentProcessor;
use Pointspay\Pointspay\Model\Ipn;

class PaymentProcessorTest extends TestCase
{
    private $transactionBuilder;

    private $orderSender;

    private $historyFactory;

    private $paymentProcessor;

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessInvoiceWithCanInvoiceAndPaymentStatusCompleted()
    {
        $paymentMock = $this->createMock(Payment::class);
        $orderMock = $this->getMockBuilder(Order::class)
            ->addMethods(['setIsCustomerNotified'])
            ->onlyMethods(
                [
                    'canInvoice',
                    'getState',
                    'getPayment',
                    'getBaseCurrency',
                    'prepareInvoice',
                    'addStatusHistoryComment',
                    'save',
                    'formatPriceTxt'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->method('save')->willReturnSelf();
        $orderMock->method('canInvoice')->willReturn(true);
        $orderMock->method('getState')->willReturn(Order::STATE_PENDING_PAYMENT);
        $orderMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $currencyMock = $this->createMock(Currency::class);
        $currencyMock->expects($this->once())->method('formatTxt')->willReturn('100.00');
        $orderMock->expects($this->once())->method('getBaseCurrency')->willReturn($currencyMock);

        $gatewayData = [
            Ipn::STATUS => Ipn::PAYMENTSTATUS_COMPLETED,
            'payment_id' => 'aaaaaabbbbbbcccccc',
            'order_id' => '1234567890',
        ];

        $this->transactionBuilder->expects($this->any())->method('setPayment')->willReturnSelf();
        $this->transactionBuilder->expects($this->any())->method('setOrder')->willReturnSelf();
        $this->transactionBuilder->expects($this->any())->method('setTransactionId')->willReturnSelf();
        $this->transactionBuilder->expects($this->any())->method('setAdditionalInformation')->willReturnSelf();
        $this->transactionBuilder->expects($this->any())->method('setFailSafe')->willReturnSelf();
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->expects($this->once())->method('setParentTxnId')->with('aaaaaabbbbbbcccccc');
        $transactionMock->expects($this->once())->method('setIsClosed')->with(0);
        $transactionMock->expects($this->once())->method('save');

        $this->transactionBuilder->expects($this->any())->method('build')->willReturn($transactionMock);

        $invoiceMock = $this->createMock(Invoice::class);
        $invoiceMock->expects($this->any())->method('getOrder')->willReturn($orderMock);
        $invoiceMock->expects($this->any())->method('register')->willReturnSelf();
        $orderMock->expects($this->once())->method('prepareInvoice')->willReturn($invoiceMock);
        $historyMock = $this->createMock(History::class);
        $historyMock->expects($this->once())->method('setComment')->willReturnSelf();
        $historyMock->expects($this->once())->method('setEntityName')->willReturnSelf();
        $historyMock->expects($this->once())->method('setOrder')->willReturnSelf();
        $historyMock->expects($this->any())->method('save')->willReturnSelf();
        $this->historyFactory->expects($this->once())->method('create')->willReturn($historyMock);
        $orderMock->expects($this->once())->method('addStatusHistoryComment')->willReturnSelf();
        $orderMock->expects($this->once())->method('setIsCustomerNotified')->willReturn($historyMock);
        $this->paymentProcessor = new PaymentProcessor(
            $this->transactionBuilder,
            $this->orderSender,
            $this->historyFactory
        );
        $this->assertTrue($this->paymentProcessor->processInvoice($orderMock, $gatewayData));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessInvoiceWithoutCanInvoice()
    {
        $order = $this->createMock(Order::class);
        $order->method('canInvoice')->willReturn(false);

        $gatewayData = [Ipn::STATUS => Ipn::PAYMENTSTATUS_COMPLETED];

        $this->paymentProcessor = new PaymentProcessor(
            $this->transactionBuilder,
            $this->orderSender,
            $this->historyFactory
        );
        $this->assertTrue($this->paymentProcessor->processInvoice($order, $gatewayData));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProcessInvoiceWithPaymentStatusNotCompleted()
    {
        $order = $this->createMock(Order::class);
        $order->method('canInvoice')->willReturn(true);

        $gatewayData = [Ipn::STATUS => 'not_completed'];
        $this->paymentProcessor = new PaymentProcessor(
            $this->transactionBuilder,
            $this->orderSender,
            $this->historyFactory
        );
        $this->assertTrue($this->paymentProcessor->processInvoice($order, $gatewayData));
    }

    protected function setUp(): void
    {
        $this->transactionBuilder = $this->createMock(BuilderInterface::class);
        $this->orderSender = $this->createMock(OrderSender::class);
        $this->historyFactory = $this->createMock(HistoryFactory::class);


    }
}
