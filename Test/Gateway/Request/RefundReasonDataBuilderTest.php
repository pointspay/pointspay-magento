<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Creditmemo;
use Pointspay\Pointspay\Gateway\Request\RefundReasonDataBuilder;

class RefundReasonDataBuilderTest extends TestCase
{

    private $refundReason;

    protected function setUp(): void
    {
        $this->refundReason = new RefundReasonDataBuilder();
    }
    public function testBuildWithValidData(): void
    {
        $testNote = 'Test refund reason';

        $creditMemo = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerNote'])
            ->getMock();
        $payment = $this->createMock(Payment::class);

        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $payment->method('getCreditMemo')->willReturn($creditMemo);

        $creditMemo->method('getCustomerNote')->willReturn($testNote);
        $buildSubject = ['payment' => $paymentDataObject];


        $expected = [
            'body' => [
                'refund_reason' => $testNote,
            ],
        ];

        $this->assertEquals($expected, $this->refundReason->build($buildSubject));
    }

    public function testBuildWithNoCustomerNote(): void
    {
        $testNote = '';

        $creditMemo = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerNote'])
            ->getMock();
        $payment = $this->createMock(Payment::class);
        $creditMemo->method('getCustomerNote')->willReturn($testNote);

        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $payment->method('getCreditMemo')->willReturn($creditMemo);

        $buildSubject = ['payment' => $paymentDataObject];

        $expected = [
            'body' => [
                'refund_reason' => 'refund',
            ],
        ];

        $this->assertEquals($expected, $this->refundReason->build($buildSubject));
    }
}
