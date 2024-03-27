<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\Order\OrderAdapter as Order;
use Pointspay\Pointspay\Gateway\Request\AmountDataBuilder;
use Pointspay\Pointspay\Service\FormatAmount;

class AmountDataBuilderTest extends TestCase
{
    private $formatAmount;
    private $amountDataBuilder;

    protected function setUp(): void
    {
        $this->formatAmount = $this->createMock(FormatAmount::class);
        $this->amountDataBuilder = new AmountDataBuilder($this->formatAmount);
    }

    public function testBuildWithValidData(): void
    {
        $totalDue = 100.00;
        $currencyCode = 'USD';
        $formattedAmount = '100.00';
        $order = $this->createMock(Order::class);
        $order->method('getGrandTotalAmount')->willReturn($totalDue);
        $order->method('getCurrencyCode')->willReturn($currencyCode);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $this->formatAmount->method('formatAmount')
            ->with($totalDue, $currencyCode)
            ->willReturn($formattedAmount);

        $expected = [
            'body' => [
                'amount' => $formattedAmount,
            ],
        ];

        $this->assertEquals($expected, $this->amountDataBuilder->build($buildSubject));
    }

    public function testBuildWithNoTotalDue(): void
    {
        $totalDue = null;
        $currencyCode = 'USD';
        $order = $this->createMock(Order::class);
        $order->method('getGrandTotalAmount')->willReturn($totalDue);
        $order->method('getCurrencyCode')->willReturn($currencyCode);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $this->formatAmount->method('formatAmount')
            ->with($totalDue, $currencyCode)
            ->willReturn(null);

        $expected = [
            'body' => [
                'amount' => null,
            ],
        ];

        $this->assertEquals($expected, $this->amountDataBuilder->build($buildSubject));
    }
}
