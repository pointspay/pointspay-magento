<?php
namespace Pointspay\Pointspay\Test\Model\Payment\Model\Method;

use Magento\Payment\Model\Info;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Payment\Model\MethodInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Model\Method\Adapter;
use Pointspay\Pointspay\Model\Payment\Model\Method\InstanceFactoryPlugin;

class InstanceFactoryPluginTest extends TestCase {
    private $instanceFactoryPlugin;
    private $info;
    private $methodInterface;
    private $configHelper;

    protected function setUp(): void
    {
        $this->info = $this->createMock(Info::class);
        $this->methodInterface = $this->createMock(MethodInterface::class);
        $this->configHelper = $this->createMock(Config::class);
        $this->instanceFactoryPlugin = new InstanceFactoryPlugin($this->configHelper);
    }
    public function testMethodInstanceIsProcessedCorrectly()
    {
        $this->info = $this->getMockBuilder(\Magento\Payment\Model\PaymentMethod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethodsDetails = [
            'pointspay_required_settings' => [
                'pointspay_code'=>'pointspay',
                'name'=>'POINTSPAY',
                'code'=>'pointspay_required_settings',
                'logo'=>'logo1',
                'isActive'=>true
            ],
            'fbp_required_settings' => [
                'pointspay_code'=>'fbp',
                'name'=>'FBP',
                'code'=>'fbp_required_settings',
                'logo'=>'logo2',
                'isActive'=>true
            ]
        ];
        $this->configHelper->method('getEnabledPaymentMethodsDetails')->willReturn($paymentMethodsDetails);

        $this->info->method('getCode')->willReturn('fbp_required_settings');

        $this->methodInterface = $this->createPartialMock(Adapter::class, ['getCode']);
        $this->methodInterface->method('getCode')->willReturn('pointspay_required_settings');

        $this->instanceFactoryPlugin = new InstanceFactoryPlugin($this->configHelper);
        $instanceFactory = $this->createMock(InstanceFactory::class);
        $result = $this->instanceFactoryPlugin->afterCreate($instanceFactory, $this->methodInterface, $this->info);

        $this->assertInstanceOf(MethodInterface::class, $result);
    }
}
