<?php

namespace Pointspay\Pointspay\Test\Gateway\Request\AdditionalData;

use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\Order\OrderAdapter as Order;
use Pointspay\Pointspay\Gateway\Request\AdditionalData\DynamicUrlsDataBuilder;
use Pointspay\Pointspay\Api\Data\ApiInterface;

class DynamicUrlsDataBuilderTest extends TestCase
{
    private $scopeConfig;

    private $dynamicUrlsDataBuilder;

    public function testBuildWithValidData(): void
    {
        $storeId = 1;
        $baseUrl = 'https://example.com/';
        $order = $this->createMock(Order::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $this->scopeConfig->method('getValue')
            ->with('web/secure/base_url', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($baseUrl);

        $expected = [
            'body' => [
                'additional_data' => [
                    'dynamic_urls' => [
                        'success' => $baseUrl . ApiInterface::POINTSPAY_SUCCESS_SUFFIX,
                        'cancel' => $baseUrl . ApiInterface::POINTSPAY_CANCEL_SUFFIX,
                        'failure' => $baseUrl . ApiInterface::POINTSPAY_FAIL_SUFFIX,
                        'ipn' => $baseUrl . ApiInterface::REST_IPN_SUFFIX,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $this->dynamicUrlsDataBuilder->build($buildSubject));
    }

    public function testBuildWithEmptyBaseUrl(): void
    {
        $storeId = 1;
        $baseUrl = '';
        $order = $this->createMock(Order::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $this->scopeConfig->method('getValue')
            ->with('web/secure/base_url', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($baseUrl);

        $expected = [
            'body' => [
                'additional_data' => [
                    'dynamic_urls' => [
                        'success' => $baseUrl . ApiInterface::POINTSPAY_SUCCESS_SUFFIX,
                        'cancel' => $baseUrl . ApiInterface::POINTSPAY_CANCEL_SUFFIX,
                        'failure' => $baseUrl . ApiInterface::POINTSPAY_FAIL_SUFFIX,
                        'ipn' => $baseUrl . ApiInterface::REST_IPN_SUFFIX,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $this->dynamicUrlsDataBuilder->build($buildSubject));
    }

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->dynamicUrlsDataBuilder = new DynamicUrlsDataBuilder($this->scopeConfig);
    }
}
