<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Data\Order\OrderAdapter as Order;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\LanguageDataBuilder;

class LanguageDataBuilderTest extends TestCase
{
    private $storeManager;

    private $scopeConfig;

    private $languageDataBuilder;

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->languageDataBuilder = new LanguageDataBuilder($this->storeManager, $this->scopeConfig);
    }

    public function testBuildWithValidData(): void
    {
        $storeId = 1;
        $languageCode = 'en_US';
        $order = $this->createMock(Order::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $this->scopeConfig->method('getValue')
            ->with('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($languageCode);

        $expected = [
            'body' => [
                'language' => 'en',
            ],
        ];

        $this->assertEquals($expected, $this->languageDataBuilder->build($buildSubject));
    }

    public function testBuildWithNoLanguageCode(): void
    {
        $storeId = 1;
        $order = $this->createMock(Order::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $this->scopeConfig->method('getValue')
            ->with('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn(null);

        $expected = [
            'body' => [
                'language' => 'en',
            ],
        ];

        $this->assertEquals($expected, $this->languageDataBuilder->build($buildSubject));
    }
}
