<?php
namespace Pointspay\Pointspay\Test\Model\Config\Source;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Config\Source\DemoMode;

class DemoModeTest extends TestCase
{
    private $demoMode;

    protected function setUp(): void
    {
        $configMock = $this->createMock(\Pointspay\Pointspay\Helper\Config::class);
        $this->demoMode = new DemoMode($configMock);
    }

    public function testReturnsExpectedOptionArrayForLiveMode()
    {
        $expected = ['value' => '0', 'label' => 'Live'];
        $options = $this->demoMode->toOptionArray();
        $this->assertContains($expected, $options);
    }

    public function testReturnsExpectedOptionArrayForSandboxMode()
    {
        $expected = ['value' => '1', 'label' => 'Sandbox'];
        $options = $this->demoMode->toOptionArray();
        $this->assertContains($expected, $options);
    }

    public function testReturnsOnlyTwoOptions()
    {
        $options = $this->demoMode->toOptionArray();
        $this->assertCount(2, $options);
    }
}
