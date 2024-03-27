<?php

namespace Pointspay\Pointspay\Test\Service;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\FormatAmount;

class FormatAmountTest extends TestCase
{
    private $formatAmount;

    protected function setUp(): void
    {
        $this->formatAmount = new FormatAmount();
    }

    public function testFormatAmountReturnsExpectedValue(): void
    {
        $this->assertEquals(100, $this->formatAmount->formatAmount(1, 'USD'));
    }

    public function testDecimalNumbersReturnsExpectedValue(): void
    {
        $this->assertEquals(2, $this->formatAmount->decimalNumbers('USD'));
    }

    public function testOriginalAmountReturnsExpectedValue(): void
    {
        $this->assertEquals(1, $this->formatAmount->originalAmount(100, 'USD'));
    }

    public function testFormatAmountHandlesZeroDecimals(): void
    {
        $this->assertEquals(1, $this->formatAmount->formatAmount(1, 'JPY'));
    }

    public function testDecimalNumbersHandlesZeroDecimals(): void
    {
        $this->assertEquals(0, $this->formatAmount->decimalNumbers('JPY'));
    }

    public function testOriginalAmountHandlesZeroDecimals(): void
    {
        $this->assertEquals(1, $this->formatAmount->originalAmount(1, 'JPY'));
    }

    public function testFormatAmountHandlesThreeDecimals(): void
    {
        $this->assertEquals(1000, $this->formatAmount->formatAmount(1, 'BHD'));
    }

    public function testDecimalNumbersHandlesThreeDecimals(): void
    {
        $this->assertEquals(3, $this->formatAmount->decimalNumbers('BHD'));
    }

    public function testOriginalAmountHandlesThreeDecimals(): void
    {
        $this->assertEquals(1, $this->formatAmount->originalAmount(1000, 'BHD'));
    }

    public function testFormatAmountHandlesTwoDecimals(): void
    {
        $this->assertEquals(100, $this->formatAmount->formatAmount(1, 'MRO'));
    }
    public function testOriginalAmountHandlesTwoDecimals(): void
    {
        $this->assertEquals(1, $this->formatAmount->originalAmount(1000, 'BHD'));
    }
}
