<?php

namespace Pointspay\Pointspay\Test\Service\Logger;

use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Helper\Config;
use Monolog\Handler\TestHandler;

class LoggerTest extends TestCase
{
    private $storeManager;

    private $config;

    private $logger;

    private $testHandler;

    public function testAddDebugLogWhenDebugModeIsEnabled()
    {
        $this->config->method('getDebugMode')->willReturn(true);
        $this->logger->addDebug('Test debug message');
        $this->assertTrue($this->testHandler->hasDebug('Test debug message'));
    }

    public function testDoesNotAddDebugLogWhenDebugModeIsDisabled()
    {
        $this->config->method('getDebugMode')->willReturn(false);
        $this->logger->addDebug('Test debug message');
        $this->assertFalse($this->testHandler->hasDebug('Test debug message'));
    }

    public function testAddInfoLogWhenDebugModeIsEnabled()
    {
        $this->config->method('getDebugMode')->willReturn(true);
        $this->logger->addInfo('Test info message');
        $this->assertTrue($this->testHandler->hasInfo('Test info message'));
    }

    public function testDoesNotAddInfoLogWhenDebugModeIsDisabled()
    {
        $this->config->method('getDebugMode')->willReturn(false);
        $this->logger->addInfo('Test info message');
        $this->assertFalse($this->testHandler->hasInfo('Test info message'));
    }

    public function testAddWarningLogRegardlessOfDebugMode()
    {
        $this->config->method('getDebugMode')->willReturn(true);
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);
        $this->logger->addWarning('Test warning message');

        $this->assertTrue($this->testHandler->hasWarning('Test warning message'));
    }
    public function testAddWarningLogRegardlessOfDebugModeButReturnFalse()
    {
        $this->config->method('getDebugMode')->willReturn(false);
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);
        $this->logger->addWarning('Test warning message');

        $this->assertFalse($this->testHandler->hasWarning('Test warning message'));
    }

    public function testAddResultLogRegardlessOfDebugMode()
    {
        $this->testHandler = new \Pointspay\Pointspay\Test\Service\Logger\LoggerTest\HandlerTest();
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);

        $this->config->method('getDebugMode')->willReturn(true);
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);
        $this->logger->addResult('Test result message');

        $this->assertTrue($this->testHandler->hasResult('Test result message'));
    }
    public function testAddResultLogRegardlessOfDebugModeButReturnFalse()
    {
        $this->testHandler = new \Pointspay\Pointspay\Test\Service\Logger\LoggerTest\HandlerTest();
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);


        $this->config->method('getDebugMode')->willReturn(false);
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);
        $this->logger->addResult('Test result message');

        $this->assertFalse($this->testHandler->hasResult('Test result message'));
    }
    public function testAddRequestLogRegardlessOfDebugMode()
    {
        $this->testHandler = new \Pointspay\Pointspay\Test\Service\Logger\LoggerTest\HandlerTest();
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);

        $this->config->method('getDebugMode')->willReturn(true);
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);
        $this->logger->addRequest('Test request message');

        $this->assertTrue($this->testHandler->hasRequest('Test request message'));
    }
    public function testAddRequestLogRegardlessOfDebugModeButReturnFalse()
    {
        $this->testHandler = new \Pointspay\Pointspay\Test\Service\Logger\LoggerTest\HandlerTest();
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);


        $this->config->method('getDebugMode')->willReturn(false);
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);
        $this->logger->addRequest('Test request message');

        $this->assertFalse($this->testHandler->hasRequest('Test request message'));
    }

    public function testAddErrorLogRegardlessOfDebugMode()
    {
        $this->logger->addError('Test error message');
        $this->assertTrue($this->testHandler->hasError('Test error message'));
    }

    public function testAddCriticalLogRegardlessOfDebugMode()
    {
        $this->logger->addCritical('Test critical message');
        $this->assertTrue($this->testHandler->hasCritical('Test critical message'));
    }

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->testHandler = new TestHandler();
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager->method('getStore')->willReturn($storeMock);
        $this->logger = new Logger($this->storeManager, $this->config, 'test', [$this->testHandler], []);
    }
}
