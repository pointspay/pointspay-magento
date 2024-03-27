<?php
namespace Pointspay\Pointspay\Test\Model\Ui;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Model\Ui\PointspayVirtualConfigProvider;
use Pointspay\Pointspay\Service\Logger\Logger;

class PointspayVirtualConfigProviderTest extends TestCase
{
    private $configMock;
    private $pointspayVirtualConfigProvider;

    /**
     * @var (\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject|\Pointspay\Pointspay\Service\Logger\Logger|(\Pointspay\Pointspay\Service\Logger\Logger&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Pointspay\Pointspay\Service\Logger\Logger&\PHPUnit\Framework\MockObject\MockObject)
     */
    private $logger;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->logger = $this->createMock(Logger::class);
        $this->logger->expects($this->any())->method('addInfo')->willReturnSelf();
        $this->pointspayVirtualConfigProvider = new PointspayVirtualConfigProvider($this->configMock, $this->logger);
    }

    public function testConfigReturnsEnabledGeneralPayment()
    {
        $this->configMock->method('isEnabledGeneralPayment')->willReturn(true);
        $this->configMock->expects($this->any())->method('getEnabledPaymentMethodsDetails')->willReturn(['pointspay_required_settings' => ['code'=>'pointspay_required_settings','logo'=>'logo1','isActive'=>true], 'fbp_required_settings' => ['code'=>'fbp_required_settings','logo'=>'logo2','isActive'=>true]]);
        $config = $this->pointspayVirtualConfigProvider->getConfig();

        $this->assertTrue($config['payment'][PointspayVirtualConfigProvider::CODE]['isActive']);
    }

    public function testConfigReturnsPaymentMethodsDetails()
    {
        $paymentMethodsDetails = ['pointspay_required_settings' => ['code'=>'pointspay_required_settings','logo'=>'logo1','isActive'=>true], 'fbp_required_settings' => ['code'=>'fbp_required_settings','logo'=>'logo2','isActive'=>true]];
        $this->configMock->expects($this->any())->method('getEnabledPaymentMethodsDetails')->willReturn($paymentMethodsDetails);

        $config = $this->pointspayVirtualConfigProvider->getConfig();

        $this->assertEquals($paymentMethodsDetails, $config['payment']['pointspay_available_methods_details']);
    }
}
