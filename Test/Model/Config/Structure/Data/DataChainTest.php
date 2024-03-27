<?php

namespace Pointspay\Pointspay\Test\Model\Config\Structure\Data;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Config\Structure\Data\DataChain;
use Pointspay\Pointspay\Api\Data\StructureDataUpdaterInterface;

class DataChainTest extends TestCase
{
    private $dataChain;

    protected function setUp(): void
    {

    }

    public function testExecuteWithValidData(): void
    {
        $config = ['configData'];
        $newConfig = ['newConfigData'];
        $link = $this->createMock(StructureDataUpdaterInterface::class);
        $link->method('execute')->with($config)->willReturn($newConfig);

        $this->dataChain = new DataChain([$link]);

        $this->assertEquals($newConfig, $this->dataChain->execute($config));
    }

    public function testExecuteWithMultipleLinks(): void
    {
        $config = ['configData'];
        $newConfig1 = ['newConfigData1'];
        $newConfig2 = ['newConfigData2'];
        $link1 = $this->createMock(StructureDataUpdaterInterface::class);
        $link1->method('execute')->with($config)->willReturn($newConfig1);
        $link2 = $this->createMock(StructureDataUpdaterInterface::class);
        $link2->method('execute')->with($newConfig1)->willReturn($newConfig2);

        $this->dataChain = new DataChain([$link1,$link2]);

        $this->assertEquals($newConfig2, $this->dataChain->execute($config));
    }

    public function testExecuteWithNoLinks(): void
    {
        $config = ['configData'];
        $this->dataChain = new DataChain([]);
        $this->assertEquals($config, $this->dataChain->execute($config));
    }
}
