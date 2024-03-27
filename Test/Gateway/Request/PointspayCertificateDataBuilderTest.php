<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\Order\OrderAdapter as Order;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\PointspayCertificateDataBuilder;
use Pointspay\Pointspay\Helper\Config;
class PointspayCertificateDataBuilderTest extends TestCase
{
    private $config;
    private $pointspayCertificateDataBuilder;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->pointspayCertificateDataBuilder = new PointspayCertificateDataBuilder($this->config);
    }

    public function testCertificateGenerationWithValidCertificate(): void
    {
        $certificate = 'certificate123';
        $paymentCode = 'paymentCode123';
        $storeId = 1;

        $order = $this->createMock(Order::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $payment->method('getMethod')->willReturn($paymentCode);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $this->config->method('getPointspayCertificate')->willReturn($certificate);

        $expected = [
            'clientConfig' => [
                'key_info' => [
                    'certificate' => $certificate,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->pointspayCertificateDataBuilder->build($buildSubject));
    }

    public function testCertificateGenerationWithNoCertificate(): void
    {
        $certificate = '';
        $storeId = 1;
        $paymentCode = 'paymentCode123';

        $order = $this->createMock(Order::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $payment->method('getMethod')->willReturn($paymentCode);
        $paymentDataObject->method('getPayment')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];

        $this->config->method('getPointspayCertificate')->willReturn($certificate);

        $expected = [
            'clientConfig' => [
                'key_info' => [
                    'certificate' => $certificate,
                ],
            ],
        ];

        $this->assertEquals($expected, $this->pointspayCertificateDataBuilder->build($buildSubject));
    }
}
