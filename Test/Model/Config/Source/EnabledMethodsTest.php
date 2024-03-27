<?php
namespace Pointspay\Pointspay\Test\Model\Config\Source;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Config\Source\EnabledMethods;
use Pointspay\Pointspay\Service\PaymentsReader;

class EnabledMethodsTest extends TestCase
{
    private $enabledMethods;
    private $paymentsReader;

    protected function setUp(): void
    {
        $this->paymentsReader = $this->createMock(PaymentsReader::class);
        $this->enabledMethods = new EnabledMethods($this->paymentsReader);
    }

    public function testReturnsOptionArrayWithAvailableMethods()
    {
        $methods = [
            ['pointspay_code' => 'method1', 'name' => 'Method 1'],
            ['pointspay_code' => 'method2', 'name' => 'Method 2'],
        ];
        $this->paymentsReader->method('getAvailablePointspayMethods')->willReturn($methods);

        $expected = [
            ['value' => 'method1', 'label' => 'Method 1'],
            ['value' => 'method2', 'label' => 'Method 2'],
        ];
        $options = $this->enabledMethods->toOptionArray();
        $this->assertEquals($expected, $options);
    }

    public function testReturnsEmptyOptionArrayWhenNoMethodsAvailable()
    {
        $this->paymentsReader->method('getAvailablePointspayMethods')->willReturn([]);

        $options = $this->enabledMethods->toOptionArray();
        $this->assertEmpty($options);
    }
}
