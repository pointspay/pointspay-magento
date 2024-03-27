<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Payment\Gateway\Data\Order\OrderAdapter as Order;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\PublicKeyDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Pointspay\Pointspay\Service\CertificateHandler;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Helper\Config;

class PublicKeyDataBuilderTest extends TestCase
{
    private $certificateHandler;
    private $storeManager;
    private $configHelper;
    private $privateKeyDataBuilder;

    protected function setUp(): void
    {
        $this->certificateHandler = $this->createMock(CertificateHandler::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->configHelper = $this->createMock(Config::class);
        $this->privateKeyDataBuilder = new PublicKeyDataBuilder($this->certificateHandler, $this->storeManager, $this->configHelper);
    }

    public function testPublicKeyGenerationWithValidPrivateKey(): void
    {
        $certificate = 'privateKey123';
        $paymentMethod = 'paymentCode123';
        $storeId = 1;
        $websiteId = 1;

        $order = $this->createMock(Order::class);
        $order->method('getStoreId')->willReturn($storeId);

        $keychain = $this->createMock(\Pointspay\Pointspay\Model\FlavourKeys::class);
        $keychain->method('getCertificate')->willReturn($certificate);

        $this->certificateHandler->method('get')->willReturn($keychain);

        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $payment->method('getMethod')->willReturn($paymentMethod);

        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $paymentDataObject->method('getOrder')->willReturn($order);

        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturn($websiteId);
        $this->storeManager->method('getWebsite')->with($storeId)->willReturn($website);

        $store = $this->createMock(StoreInterface::class);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);


        $buildSubject = ['payment' => $paymentDataObject];
        $expected = [
            'clientConfig' => [
                'key_info' => [
                    'public_key' => $certificate,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->privateKeyDataBuilder->build($buildSubject));
    }

    public function testPublicKeyGenerationWithNoPrivateKey(): void
    {
        $publicKey = '';
        $paymentMethod = 'paymentCode123';
        $storeId = 1;
        $websiteId = 1;

        $order = $this->createMock(Order::class);
        $order->method('getStoreId')->willReturn($storeId);

        $keychain = $this->createMock(\Pointspay\Pointspay\Model\FlavourKeys::class);
        $keychain->method('getCertificate')->willReturn($publicKey);

        $this->certificateHandler->method('get')->willReturn($keychain);

        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $payment->method('getMethod')->willReturn($paymentMethod);

        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $paymentDataObject->method('getOrder')->willReturn($order);

        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturn($websiteId);
        $this->storeManager->method('getWebsite')->with($storeId)->willReturn($website);

        $store = $this->createMock(StoreInterface::class);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);


        $buildSubject = ['payment' => $paymentDataObject];

        $expected = [
            'clientConfig' => [
                'key_info' => [
                    'public_key' => $publicKey,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->privateKeyDataBuilder->build($buildSubject));
    }
}
