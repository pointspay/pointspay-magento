<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\TimestampDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;

class TimestampDataBuilderTest extends TestCase
{
    private $timestampDataBuilder;

    protected function setUp(): void
    {
        $this->timestampDataBuilder = new TimestampDataBuilder();
    }

    public function testTimestampGenerationWithValidTime(): void
    {
        $payment = $this->createMock(PaymentDataObject::class);
        $buildSubject = ['payment' => $payment];

        $result = $this->timestampDataBuilder->build($buildSubject);

        $this->assertArrayHasKey('clientConfig', $result);
        $this->assertArrayHasKey('oauth', $result['clientConfig']);
        $this->assertArrayHasKey('timestamp', $result['clientConfig']['oauth']);
        $this->assertIsInt($result['clientConfig']['oauth']['timestamp']);
    }
}
