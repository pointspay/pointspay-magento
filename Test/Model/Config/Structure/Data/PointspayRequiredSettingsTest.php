<?php

namespace Pointspay\Pointspay\Test\Model\Config\Structure\Data;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Config\Structure\Data\PointspayRequiredSettings;
use Magento\Framework\Stdlib\ArrayManager;
use Pointspay\Pointspay\Service\Logger\Logger;
use Pointspay\Pointspay\Service\PaymentsReader;
use Psr\Log\LoggerInterface;

class PointspayRequiredSettingsTest extends TestCase
{
    private $arrayManager;

    private $paymentsReader;

    private $logger;

    private $pointspayRequiredSettings;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    public function testExecuteWithData(): void
    {
        $pathHere = realpath(__DIR__);
        $pathForFixture = $pathHere . '/_files/PointspayRequiredSettingsTest/config_fixture.txt';
        $content = file_get_contents($pathForFixture);
        $config = json_decode($content, true);
        $pathForFixtureResult = $pathHere . '/_files/PointspayRequiredSettingsTest/config_fixture_result.txt';
        $contentResult = file_get_contents($pathForFixtureResult);
        $newConfig = json_decode($contentResult, true);

        $this->paymentsReader->method('getAvailablePointspayMethods')->willReturn([
            ['pointspay_code' => 'pointspay_required_settings', 'name' => 'Pointspay'],
            ['pointspay_code' => 'fbp', 'name' => 'Flying Blue+']
        ]);

        $this->arrayManager->method('findPath')->willReturn('config/pointspay_group_all_in_one');
        $this->arrayManager->method('exists')->willReturn(false);
        $this->arrayManager->method('set')->willReturn($newConfig);

        $logger = $this->objectManagerHelper->getObject(Logger::class,['name'=>'testLogger']);
        $arrayManager = $this->objectManagerHelper->getObject(ArrayManager::class);
        /** @var \Pointspay\Pointspay\Model\Config\Structure\Data\PointspayAccessSettings $pointspayAccessSettings */
        $pointspayAccessSettings = $this->objectManagerHelper->getObject(
            PointspayRequiredSettings::class,
            [
                'arrayManager' => $arrayManager,
                'paymentsReader' => $this->paymentsReader,
                'logger' => $logger
            ]
        );
        $result = $pointspayAccessSettings->execute($config);
        $this->assertEquals($newConfig, $result);
    }

    protected function setUp(): void
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);

        ObjectManager::setInstance($objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->arrayManager = $this->createMock(ArrayManager::class);
        $this->paymentsReader = $this->createMock(PaymentsReader::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pointspayRequiredSettings = new PointspayRequiredSettings($this->arrayManager, $this->paymentsReader, $this->logger);
    }
}
