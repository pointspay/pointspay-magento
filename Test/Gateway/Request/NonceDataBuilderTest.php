<?php

namespace Pointspay\Pointspay\Test\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Request\NonceDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Pointspay\Pointspay\Service\Uuid;

class NonceDataBuilderTest extends TestCase
{
    private $nonceDataBuilder;

    private $uuid;

    public function testNonceGenerationExist(): void
    {
        $uuid = 'uuid123';
        $storeId = 1;
        $order = $this->createMock(OrderAdapter::class);
        $order->method('getStoreId')->willReturn($storeId);
        $paymentDataObject = $this->createMock(PaymentDataObject::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDataObject];
        $uuidResult = $this->nonceDataBuilder->build($buildSubject);
        $this->assertIsArray($uuidResult);
        $this->assertIsString($uuidResult['clientConfig']['oauth']['nonce']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Mock the Uuid class
        $this->uuid = new Uuid();
        // Instantiate the NonceDataBuilder with the mocked Uuid
        $this->nonceDataBuilder = new NonceDataBuilder($this->uuid);
    }
}
