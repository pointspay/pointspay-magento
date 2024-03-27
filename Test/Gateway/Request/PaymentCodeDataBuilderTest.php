<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\PaymentCodeDataBuilder;

class PaymentCodeDataBuilderTest extends TestCase
{
    private $paymentCodeDataBuilder;

    protected function setUp(): void
    {
        $this->paymentCodeDataBuilder = new PaymentCodeDataBuilder();
    }

    public function testPaymentCodeGenerationWithValidMethod(): void
    {
        $paymentMethod = 'method123';
        $storeId = 1;
        $order = $this->createMock(OrderAdapter::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentDataObject->method('getPayment')->willReturn($payment);


        $buildSubject = ['payment' => $paymentDataObject];

        $expected = [
            'clientConfig' => [
                'payment_code' => $paymentMethod,
            ],
        ];

        $this->assertEquals($expected, $this->paymentCodeDataBuilder->build($buildSubject));
    }

    public function testPaymentCodeGenerationWithNoMethod(): void
    {
        $paymentMethod = '';
        $storeId = 1;
        $order = $this->createMock(OrderAdapter::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentDataObject->method('getPayment')->willReturn($payment);


        $buildSubject = ['payment' => $paymentDataObject];

        $expected = [
            'clientConfig' => [
                'payment_code' => $paymentMethod,
            ],
        ];

        $this->assertEquals($expected, $this->paymentCodeDataBuilder->build($buildSubject));
    }
}
