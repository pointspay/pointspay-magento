<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\ShopCodeDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Helper\Config;
use Magento\Store\Api\Data\WebsiteInterface;

class ShopCodeDataBuilderTest extends TestCase
{
    private $storeManager;
    private $config;
    private $shopCodeDataBuilder;

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->shopCodeDataBuilder = new ShopCodeDataBuilder($this->storeManager, $this->config);
    }

    public function testBuildWithValidShopCode(): void
    {
        $storeId = 1;
        $websiteId = 1;
        $shopCode = 'shopCode123';
        $paymentCode = 'paymentCode123';
        $order = $this->createMock(OrderAdapter::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $payment->method('getMethod')->willReturn($paymentCode);
        $paymentDataObject->method('getPayment')->willReturn($payment);

        $buildSubject = ['payment' => $paymentDataObject];

        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturn($websiteId);
        $this->storeManager->method('getWebsite')->with($storeId)->willReturn($website);

        $this->config->method('getShopCode')
            ->with($paymentCode, $websiteId)
            ->willReturn($shopCode);

        $expected = [
            'body' => [
                'shop_code' => $shopCode,
            ],
        ];

        $this->assertEquals($expected, $this->shopCodeDataBuilder->build($buildSubject));
    }

    public function testBuildWithEmptyShopCode(): void
    {
        $storeId = 1;
        $websiteId = 1;
        $shopCode = '';
        $paymentCode = 'paymentCode123';
        $order = $this->createMock(OrderAdapter::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $payment->method('getMethod')->willReturn($paymentCode);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $buildSubject = ['payment' => $paymentDataObject];

        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturn($websiteId);
        $this->storeManager->method('getWebsite')->with($storeId)->willReturn($website);

        $this->config->method('getShopCode')
            ->with($paymentCode, $websiteId)
            ->willReturn($shopCode);

        $expected = [
            'body' => [
                'shop_code' => $shopCode,
            ],
        ];

        $this->assertEquals($expected, $this->shopCodeDataBuilder->build($buildSubject));
    }
}
