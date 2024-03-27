<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\PaymentIdRefundDataBuilder;

class PaymentIdRefundDataBuilderTest extends TestCase
{
    private $paymentIdRefundDataBuilder;

    protected function setUp(): void
    {
        $this->paymentIdRefundDataBuilder = new PaymentIdRefundDataBuilder();
    }

    public function testBuildWithValidPaymentDataObject()
    {
        $paymentDO = $this->createMock(PaymentDataObject::class);
        $paymentDO->method('getPayment')->willReturn($this->createMock(\Magento\Sales\Model\Order\Payment::class));

        $buildSubject = ['payment' => $paymentDO];

        $result = $this->paymentIdRefundDataBuilder->build($buildSubject);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('body', $result);
        $this->assertArrayHasKey('payment_id', $result['body']);
    }

    public function testBuildWithInvalidPaymentDataObject()
    {
        $this->expectException(\InvalidArgumentException::class);

        $buildSubject = ['payment' => 'invalid'];

        $this->paymentIdRefundDataBuilder->build($buildSubject);
    }

    public function testBuildWithEmptyBuildSubject()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->paymentIdRefundDataBuilder->build([]);
    }
}
