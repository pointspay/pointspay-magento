<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\ConsumerKeyDataBuilder;
use Pointspay\Pointspay\Helper\Config;

class ConsumerKeyDataBuilderTest extends TestCase
{
    private $storeManager;

    private $config;

    private $consumerKeyDataBuilder;

    public function testBuildWithValidConsumerKey(): void
    {
        $storeId = 1;
        $websiteId = 1;
        $consumerKey = 'consumerKey123';
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
        $store = $this->createMock(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);

        $this->config->method('getConsumerKey')
            ->with($paymentCode, $websiteId)
            ->willReturn($consumerKey);

        $expected = [
            'clientConfig' => [
                'oauth' => [
                    'consumer_key' => $consumerKey,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->consumerKeyDataBuilder->build($buildSubject));
    }

    public function testBuildWithEmptyConsumerKey(): void
    {
        $storeId = 1;
        $websiteId = 1;
        $consumerKey = '';
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
        $store = $this->createMock(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);

        $this->config->method('getConsumerKey')
            ->with($paymentCode, $websiteId)
            ->willReturn($consumerKey);

        $expected = [
            'clientConfig' => [
                'oauth' => [
                    'consumer_key' => $consumerKey,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->consumerKeyDataBuilder->build($buildSubject));
    }

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->consumerKeyDataBuilder = new ConsumerKeyDataBuilder($this->storeManager, $this->config);
    }
}
