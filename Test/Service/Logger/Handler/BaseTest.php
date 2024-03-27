<?php

namespace Pointspay\Pointspay\Test\Service\Logger\Handler;

use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Logger\Handler\Base;
use ReflectionClass;

class BaseTest extends TestCase
{
    private $filesystem;

    private $base;

    public function testLoggerHandlesCorrectLevel()
    {
        $reflection = new ReflectionClass($this->base);
        $reflectionProperty = $reflection->getProperty('level');
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($this->base);
        $record = ['level' => $value];
        $this->assertTrue($this->base->isHandling($record));
    }

    public function testLoggerDoesNotHandleIncorrectLevel()
    {
        $reflection = new ReflectionClass($this->base);
        $reflectionProperty = $reflection->getProperty('level');
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($this->base);
        $record = ['level' => $value + 1];
        $this->assertFalse($this->base->isHandling($record));
    }

    public function testLoggerFileNameIsFormattedCorrectly()
    {
        $reflection = new ReflectionClass(Base::class);
        $property = $reflection->getProperty('fileName');
        $property->setAccessible(true);
        $fileName = $property->getValue($this->base);
        $stringToAssert = 'var/log/pointspay/generic/info.log';
        $this->assertStringContainsString($stringToAssert, $fileName);
    }

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(File::class);
        $this->base = new Base($this->filesystem);
    }
}
