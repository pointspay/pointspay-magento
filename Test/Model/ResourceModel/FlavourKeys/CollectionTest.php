<?php
namespace Pointspay\Pointspay\Test\Model\ResourceModel\FlavourKeys;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\Test\Unit\ResourceModel\Db\Collection\Uut;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys\Collection;
use Psr\Log\LoggerInterface;

class CollectionTest extends TestCase {
    const TABLE_NAME = 'pointspay_keys';

    /** @var Uut */
    protected $uut;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var EntityFactoryInterface|MockObject */
    protected $entityFactoryMock;

    /** @var LoggerInterface|MockObject */
    protected $loggerMock;

    /** @var FetchStrategyInterface|MockObject */
    protected $fetchStrategyMock;

    /** @var ManagerInterface|MockObject */
    protected $managerMock;

    /** @var AbstractDb|MockObject  */
    protected $resourceMock;

    /** @var Mysql|MockObject */
    protected $connectionMock;

    /** @var Select|MockObject  */
    protected $selectMock;

    /** @var \Magento\Framework\App\ObjectManager|MockObject */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManagerBackup;

    /**
     * @var \Pointspay\Pointspay\Model\ResourceModel\FlavourKeys\Collection
     */
    private $flavourKeysCollection;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->getMockForAbstractClass(EntityFactoryInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock =
            $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $this->managerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $renderer = $this->createMock(SelectRenderer::class);
        $this->resourceMock = $this->createMock(FlagResource::class);

        $this->resourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->setMethods(['getPart', 'setPart', 'from', 'columns'])
            ->setConstructorArgs([$this->connectionMock, $renderer])
            ->getMock();

        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);

        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->uut = $this->getUut();
    }
    /**
     * @return object
     */
    protected function getUut()
    {
        return $this->objectManagerHelper->getObject(
            \Pointspay\Pointspay\Model\ResourceModel\FlavourKeys\Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->managerMock,
                'connection' => $this->connectionMock,
                // Magento\Framework\Flag\FlagResource extends Magento\Framework\Model\ResourceModel\Db\AbstractDb
                'resource' => $this->resourceMock,
            ]
        );
    }
    public function testSetMainTable()
    {
        $anotherTableName = 'another_table';

        $this->selectMock
            ->expects($this->atLeastOnce())
            ->method('getPart')
            ->willReturn(['main_table' => []]);

        $this->selectMock->expects($this->atLeastOnce())->method('setPart');

        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->willReturnMap([['', self::TABLE_NAME], [$anotherTableName, $anotherTableName]]);

        $this->uut = $this->getUut();

        $this->assertInstanceOf(Collection::class, $this->uut->setMainTable(''));
        $this->assertInstanceOf(Collection::class, $this->uut->setMainTable($anotherTableName));
        $this->assertEquals($anotherTableName, $this->uut->getMainTable());
    }
}
