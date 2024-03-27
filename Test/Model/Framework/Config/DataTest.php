<?php

namespace Pointspay\Pointspay\Test\Model\Framework\Config;

use Magento\Framework\App\Cache\Type\Config as CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json as SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Config\Reader as ReaderInterface;
use Pointspay\Pointspay\Model\Framework\Config\Data;

class DataTest extends TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    public function testDataReset()
    {
        $data = ['data'=>['key'=>'value']];
        $reader = $this->createMock(ReaderInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())->method('unserialize')->willReturn($data);
        $cache->expects($this->any())->method('load')->willReturn(json_encode($data));
        $data = $this->objectManagerHelper->getObject(Data::class, [
            'reader' => $reader,
            'cache' => $cache,
            'cacheId' => 'pointspay_payment_config',
            'serializer'=> $serializer,
        ]);
        $data->reset();
    }
    public function testDataResetClearsCache()
    {
        $data = ['data' => ['key' => 'value']];
        $reader = $this->createMock(ReaderInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())->method('unserialize')->willReturn($data);
        $cache->expects($this->any())->method('load')->willReturn(json_encode($data));
        $cache->expects($this->any())->method('clean');
        /** @var Data $data */
        $data = $this->objectManagerHelper->getObject(Data::class, [
            'reader' => $reader,
            'cache' => $cache,
            'cacheId' => 'pointspay_payment_config',
            'serializer' => $serializer,
            'cacheTags' => [
                'config' => 'config',
                'payment'=> 'payment',
                'pointspay_payment_config' => 'pointspay_payment_config'
            ]
        ]);
        $data->reset();
    }

    protected function setUp(): void
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);

        ObjectManager::setInstance($objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }
}
