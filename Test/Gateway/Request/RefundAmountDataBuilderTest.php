<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Creditmemo;
use Pointspay\Pointspay\Gateway\Request\RefundAmountDataBuilder;
use Pointspay\Pointspay\Service\FormatAmount;

class RefundAmountDataBuilderTest extends TestCase
{
    private $formatAmount;
    private $amountDataBuilder;

    protected function setUp(): void
    {
        $this->formatAmount = $this->createMock(FormatAmount::class);
        $this->amountDataBuilder = new RefundAmountDataBuilder($this->formatAmount);
    }

    public function testBuildWithValidData(): void
    {
        $totalDue = 100.00;
        $currencyCode = 'USD';
        $formattedAmount = '100.00';
        $creditMemo = $this->createMock(Creditmemo::class);
        $payment = $this->createMock(Payment::class);

        $creditMemo->method('getGrandTotal')->willReturn($totalDue);
        $creditMemo->method('getOrderCurrencyCode')->willReturn($currencyCode);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $payment->method('getCreditMemo')->willReturn($creditMemo);
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
        $creditMemo = $this->createMock(Creditmemo::class);
        $payment = $this->createMock(Payment::class);

        $creditMemo->method('getGrandTotal')->willReturn($totalDue);
        $creditMemo->method('getOrderCurrencyCode')->willReturn($currencyCode);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $payment->method('getCreditMemo')->willReturn($creditMemo);
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
