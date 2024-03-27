<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\Order\OrderAdapter as Order;
use Pointspay\Pointspay\Gateway\Request\OrderIdDataBuilder;

class OrderIdDataBuilderTest extends TestCase
{
    private $orderIdDataBuilder;

    protected function setUp(): void
    {
        $this->orderIdDataBuilder = new OrderIdDataBuilder();
    }

    public function testBuildWithValidData(): void
    {
        $orderId = 123;
        $incrementId = 'INC123';
        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn($orderId);
        $order->method('getOrderIncrementId')->willReturn($incrementId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $expected = [
            'body' => [
                'order_id' => $incrementId,
            ],
        ];

        $this->assertEquals($expected, $this->orderIdDataBuilder->build($buildSubject));
    }

    public function testBuildWithNoIncrementId(): void
    {
        $orderId = 123;
        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn($orderId);
        $order->method('getOrderIncrementId')->willReturn(null);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $expected = [
            'body' => [
                'order_id' => $orderId,
            ],
        ];

        $this->assertEquals($expected, $this->orderIdDataBuilder->build($buildSubject));
    }
}
