<?php
namespace Pointspay\Pointspay\Test\Model\Payment\Helper;

use Magento\Framework\App\Config;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Factory;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Payment\Helper\DataPlugin;

class DataPluginTest extends TestCase
{
    private $dataPlugin;
    private $data;

    /**
     * @var \Magento\Framework\App\Config|(\Magento\Framework\App\Config&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Framework\App\Config&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Payment\Model\Method\Factory|(\Magento\Payment\Model\Method\Factory&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Payment\Model\Method\Factory&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentFactoryMock;

    protected function setUp(): void
    {
        $this->data = $this->createMock(Data::class);
        $this->configMock = $this->createMock(Config::class);
        $this->paymentFactoryMock = $this->createMock(Factory::class);
        $this->dataPlugin = new DataPlugin($this->configMock, $this->paymentFactoryMock);
    }

    public function testPaymentMethodsAreFilteredCorrectly()
    {
        $methods = [
            'method1' => 'data1',
            'method2_access_settings' => 'data2',
            'pointspay_group_all_in_one' => 'data3',
            'method3' => 'data4'
        ];

        $expected = [
            'method1' => 'data1',
            'method3' => 'data4'
        ];

        $result = $this->dataPlugin->afterGetPaymentMethods($this->data, $methods);

        $this->assertEquals($expected, $result);
    }

    public function testPaymentMethodsAreReturnedAsIsWhenNoMatch()
    {
        $methods = [
            'method1' => 'data1',
            'method2' => 'data2',
            'method3' => 'data3'
        ];

        $result = $this->dataPlugin->afterGetPaymentMethods($this->data, $methods);

        $this->assertEquals($methods, $result);
    }
}
