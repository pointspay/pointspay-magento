<?php

namespace Pointspay\Pointspay\Test\Gateway\Data\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order;
use Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use Pointspay\Pointspay\Gateway\Data\Order\OrderAdapter;
use Pointspay\Pointspay\Gateway\Data\Order\OrderAdapterFactory;

class OrderAdapterFactoryTest extends TestCase
{
    private $orderMock;
    private $addressAdapterFactoryMock;
    private $orderAdapterFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock->method('create')->willReturn($this->createMock(OrderAdapter::class));
        ObjectManager::setInstance($objectManagerMock);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->orderMock = $this->createMock(Order::class);
        $this->addressAdapterFactoryMock = $this->objectManagerHelper->getObject(\Magento\Payment\Gateway\Data\Order\AddressAdapterFactory::class);
        $this->orderAdapterFactory = new OrderAdapterFactory($objectManagerMock);
    }

    public function testCreate()
    {
        $result = $this->orderAdapterFactory->create(['order' => $this->orderMock, 'addressAdapterFactory' => $this->addressAdapterFactoryMock]);

        $this->assertInstanceOf(OrderAdapter::class, $result);
    }
}
