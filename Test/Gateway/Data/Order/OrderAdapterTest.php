<?php

namespace Pointspay\Pointspay\Test\Gateway\Data\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Data\Order\OrderAdapter;
use Pointspay\Pointspay\Gateway\Data\Order\OrderAdapterFactory;

class OrderAdapterTest extends TestCase
{
    private $orderMock;

    private $addressAdapterFactoryMock;

    private $orderAdapter;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    public function testGetCurrencyCodeReturnsCorrectValue()
    {
        $this->orderMock->method('getBaseCurrencyCode')->willReturn('USD');
        $this->assertEquals('USD', $this->orderAdapter->getCurrencyCode());
    }

    public function testGetOrderIncrementIdReturnsCorrectValue()
    {
        $this->orderMock->method('getIncrementId')->willReturn('10000001');
        $this->assertEquals('10000001', $this->orderAdapter->getOrderIncrementId());
    }

    public function testGetCustomerIdReturnsCorrectValue()
    {
        $this->orderMock->method('getCustomerId')->willReturn(1);
        $this->assertEquals(1, $this->orderAdapter->getCustomerId());
    }

    public function testGetBillingAddressReturnsNullWhenNoBillingAddress()
    {
        $this->orderMock->method('getBillingAddress')->willReturn(null);
        $this->assertNull($this->orderAdapter->getBillingAddress());
    }
    public function testGetBillingAddressReturnsAddressWhenBillingAddress()
    {
        //\Magento\Sales\Api\Data\OrderAddressInterface
        $orderAddress = $this->createMock(\Magento\Sales\Api\Data\OrderAddressInterface::class);
        $this->orderMock->method('getBillingAddress')->willReturn($orderAddress);
        $this->assertNull($this->orderAdapter->getBillingAddress());
    }

    public function testGetShippingAddressReturnsNullWhenNoShippingAddress()
    {
        $this->orderMock->method('getShippingAddress')->willReturn(null);
        $this->assertNull($this->orderAdapter->getShippingAddress());
    }

    public function testGetStoreIdReturnsCorrectValue()
    {
        $this->orderMock->method('getStoreId')->willReturn(1);
        $this->assertEquals(1, $this->orderAdapter->getStoreId());
    }

    public function testGetIdReturnsCorrectValue()
    {
        $this->orderMock->method('getEntityId')->willReturn(1);
        $this->assertEquals(1, $this->orderAdapter->getId());
    }

    public function testGetGrandTotalAmountReturnsCorrectValue()
    {
        $this->orderMock->method('getBaseGrandTotal')->willReturn(100.00);
        $this->assertEquals(100.00, $this->orderAdapter->getGrandTotalAmount());
    }

    public function testGetRemoteIpReturnsCorrectValue()
    {
        $this->orderMock->method('getRemoteIp')->willReturn('127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->orderAdapter->getRemoteIp());
    }

    protected function setUp(): void
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);

        ObjectManager::setInstance($objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderMock = $this->createMock(Order::class);
        $this->addressAdapterFactoryMock = $this->objectManagerHelper->getObject(\Magento\Payment\Gateway\Data\Order\AddressAdapterFactory::class);
        $this->orderAdapter = new OrderAdapter($this->orderMock, $this->addressAdapterFactoryMock);
    }
}
