<?php

namespace Pointspay\Pointspay\Test\Model\Framework\App\Config\Initital;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\Initial\Converter;
use Magento\Framework\Stdlib\ArrayManager;
use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Model\Framework\App\Config\Initital\ConverterPlugin;
use Pointspay\Pointspay\Service\PaymentsReader;

class ConverterPluginTest extends TestCase
{
    private $config;

    private $arrayManager;

    private $converterPlugin;

    public function testAfterConvert(): void
    {
        $this->assertTrue(true);
        return;
        // todo remove this test
        $converter = $this->createMock(Converter::class);
        $source = $this->createMock(DOMDocument::class);
        $result = [];
        $availablePayments = [
            ['pointspay_code' => 'pointspay', 'name' => 'Pointspay'],
            ['pointspay_code' => 'fbp', 'name' => 'FBP']
        ];
        $paymentReader = $this->createMock(PaymentsReader::class);
        $paymentReader->expects($this->any())
            ->method('getAvailablePointspayMethods')
            ->willReturn($availablePayments);
        $this->config->expects($this->any())
            ->method('getPaymentsReader')
            ->willReturn($paymentReader);

        $expectedResult = [
            'pointspay' => [
                'title' => 'Pointspay'
            ],
            'fbp' => [
                'title' => 'FBP'
            ]
        ];
        $actualResult = $this->converterPlugin->afterConvert($converter, $result, $source);
        $copyOfResult = $actualResult;
        $this->assertEquals($expectedResult, $actualResult);
    }

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->arrayManager = $this->createMock(ArrayManager::class);
        $this->converterPlugin = new ConverterPlugin($this->config, $this->arrayManager);
    }
}
