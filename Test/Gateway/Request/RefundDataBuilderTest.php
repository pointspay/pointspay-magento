<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Pointspay\Pointspay\Gateway\Request\RefundDataBuilder;
use Psr\Log\LoggerInterface;

class RefundDataBuilderTest extends TestCase
{

    private $refundDataBuilder;

    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->refundDataBuilder = new RefundDataBuilder($this->logger);
    }

    public function testBuild(): void
    {
        $formattedAmount = '100.00';
        $txnId = 'bf3cbaea737540e0837cffcdb83dae41';
        $payment = $this->createMock(Payment::class);

        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $payment->method('getParentTransactionId')->willReturn($txnId);
        $buildSubject = ['payment' => $paymentDataObject, 'amount' => $formattedAmount];
     //   $formattedAmount = $this->amountDataObject->readAmount($formattedAmount);


        $expected =  [
            'transaction_id' => $txnId,
            'amount' => $formattedAmount
        ];

        $this->assertEquals($expected, $this->refundDataBuilder->build($buildSubject));
    }

    public function testBuildWithNoTotalDue(): void
    {
        $txnId = 'bf3cbaea737540e0837cffcdb83dae41';

        $payment = $this->createMock(Payment::class);
        $payment->method('getParentTransactionId')->willReturn($txnId);

        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $buildSubject = ['payment' => $paymentDataObject, 'amount' => null ];


        $expected =  [
            'transaction_id' => $txnId,
            'amount' => null
        ];

        $this->assertEquals($expected, $this->refundDataBuilder->build($buildSubject));
    }
}
