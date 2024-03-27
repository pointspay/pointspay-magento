<?php
namespace Pointspay\Pointspay\Test\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys;

class FlavourKeysTest extends TestCase
{
    private $flavourKeys;

    protected function setUp(): void
    {
    }

    public function testFlavourKeysInitializesCorrectly()
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->any())
            ->method('getTableName')
            ->with('pointspay_keys','default')
            ->willReturn('pointspay_keys');
        $contextMock->method('getResources')->willReturn($resourceMock);
        $this->flavourKeys = new FlavourKeys($contextMock);
        $this->assertEquals('pointspay_keys', $this->flavourKeys->getMainTable());
    }
}
