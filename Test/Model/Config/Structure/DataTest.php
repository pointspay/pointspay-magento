<?php

namespace Pointspay\Pointspay\Test\Model\Config\Structure;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Config\Structure\Data;
use Pointspay\Pointspay\Model\Config\Structure\Data\DataChain;
use Psr\Log\LoggerInterface;
use Magento\Config\Model\Config\Structure\Data as StructureData;

class DataTest extends TestCase
{
    private $logger;
    private $dataChain;
    private $data;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(\Pointspay\Pointspay\Service\Logger\Logger::class);
        $this->dataChain = $this->createMock(DataChain::class);
        $this->data = new Data($this->logger, $this->dataChain);
    }

    public function testMergeWithValidData(): void
    {
        $subject = $this->createMock(StructureData::class);
        $config = ['configData'];
        $newConfig = ['newConfigData'];

        $this->dataChain->method('execute')->with($config)->willReturn($newConfig);

        $subject->expects($this->any())->method('merge')->with($newConfig);

        $this->data->aroundMerge($subject, function () {}, $config);
    }

    public function testMergeWithException(): void
    {
        $subject = $this->createMock(StructureData::class);
        $config = ['configData'];

        $this->dataChain->method('execute')->with($config)->willThrowException(new \Exception());

        $this->logger->expects($this->any())->method('error');

        $subject->expects($this->any())->method('merge')->with($config);

        $this->data->aroundMerge($subject, function () {}, $config);
    }
}
