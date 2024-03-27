<?php

namespace Pointspay\Pointspay\Test\Service\Refund;

namespace Pointspay\Pointspay\Test\Service\Refund;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Refund\CheckoutFactory;
use Magento\Framework\ObjectManagerInterface;

class CheckoutFactoryTest extends TestCase
{
    private $checkoutFactory;
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->checkoutFactory = new CheckoutFactory($this->objectManager);
    }

    public function testCreateWithValidData()
    {
        $data = ['key' => 'value'];
        $instanceName = '\\Pointspay\\Pointspay\\Service\\Refund\\VirtualRefundService';
        $instance = $this->createMock($instanceName);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($instanceName, $data)
            ->willReturn($instance);

        $result = $this->checkoutFactory->create($data);

        $this->assertSame($instance, $result);
    }

    public function testCreateWithEmptyData()
    {
        $data = [];
        $instanceName = '\\Pointspay\\Pointspay\\Service\\Refund\\VirtualRefundService';
        $instance = $this->createMock($instanceName);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($instanceName, $data)
            ->willReturn($instance);

        $result = $this->checkoutFactory->create($data);

        $this->assertSame($instance, $result);
    }
}
