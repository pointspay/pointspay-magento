<?php
namespace Pointspay\Pointspay\Test\Model\Payment\Model;

use Magento\Payment\Model\Info;
use Magento\Payment\Model\MethodInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Model\Method\Adapter;
use Pointspay\Pointspay\Model\Payment\Model\InfoPlugin;

class InfoPluginTest extends TestCase
{
    private $infoPlugin;
    private $info;
    private $methodInterface;
    private $configHelper;

    protected function setUp(): void
    {
        $this->info = $this->createMock(Info::class);
        $this->methodInterface = $this->createMock(MethodInterface::class);
        $this->configHelper = $this->createMock(Config::class);
        $this->infoPlugin = new InfoPlugin($this->configHelper);
    }

    public function testMethodInstanceIsProcessedCorrectly()
    {
        $this->info = $this->getMockBuilder(Info::class)
            ->addMethods(['getMethod'])
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

        $this->info->method('getMethod')->willReturn('fbp_required_settings');

        $this->methodInterface = $this->createPartialMock(Adapter::class, ['getCode']);
        $this->methodInterface->method('getCode')->willReturn('pointspay_required_settings');

        $this->infoPlugin = new InfoPlugin($this->configHelper);
        $result = $this->infoPlugin->afterGetMethodInstance($this->info, $this->methodInterface);

        $this->assertInstanceOf(MethodInterface::class, $result);
    }
}
