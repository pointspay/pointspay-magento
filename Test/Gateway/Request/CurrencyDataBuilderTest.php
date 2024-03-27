<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\Order\OrderAdapter as Order;
use Pointspay\Pointspay\Gateway\Request\CurrencyDataBuilder;

class CurrencyDataBuilderTest extends TestCase
{
    private $currencyDataBuilder;

    protected function setUp(): void
    {
        $this->currencyDataBuilder = new CurrencyDataBuilder();
    }

    public function testBuildWithValidData(): void
    {
        $currencyCode = 'USD';
        $order = $this->createMock(Order::class);
        $order->method('getCurrencyCode')->willReturn($currencyCode);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $expected = [
            'body' => [
                'currency' => $currencyCode,
            ],
        ];

        $this->assertEquals($expected, $this->currencyDataBuilder->build($buildSubject));
    }

    public function testBuildWithNoCurrencyCode(): void
    {
        $order = $this->createMock(Order::class);
        $order->method('getCurrencyCode')->willReturn(null);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $expected = [
            'body' => [
                'currency' => null,
            ],
        ];

        $this->assertEquals($expected, $this->currencyDataBuilder->build($buildSubject));
    }
}
