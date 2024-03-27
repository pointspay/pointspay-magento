<?php

namespace Pointspay\Pointspay\Test\Service;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\PaymentMethodsUpdater;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Pointspay\Pointspay\Api\Data\ApiInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Service\PaymentMethodsUpdater\ExecutionChain;

class PaymentMethodsUpdaterTest extends TestCase
{
    private $configCacheType;
    private $filesystemIo;
    private $moduleReader;
    private $api;
    private $serializer;
    private $executionChainDataModifier;
    private $paymentMethodsUpdater;

    /**
     * @var (\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject|\Pointspay\Pointspay\Service\Logger\Logger|(\Pointspay\Pointspay\Service\Logger\Logger&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Pointspay\Pointspay\Service\Logger\Logger&\PHPUnit\Framework\MockObject\MockObject)
     */
    private $logger;

    protected function setUp(): void
    {
        $this->configCacheType = $this->createMock(Config::class);
        $this->filesystemIo = $this->createMock(File::class);
        $this->moduleReader = $this->createMock(Reader::class);
        $this->api = $this->createMock(ApiInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->executionChainDataModifier = $this->createMock(ExecutionChain::class);
        $this->logger = $this->createMock(\Pointspay\Pointspay\Service\Logger\Logger::class);
        $this->paymentMethodsUpdater = new PaymentMethodsUpdater(
            $this->configCacheType,
            $this->moduleReader,
            $this->filesystemIo,
            $this->api,
            $this->serializer,
            $this->executionChainDataModifier,
            $this->logger
        );
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testPaymentMethodsUpdaterExecutesWithoutError(): void
    {
        $this->api->method('getPaymentMethods')->willReturn([]);
        $this->assertNull($this->paymentMethodsUpdater->execute());
    }

    public function testCreateXmlByDataReturnsExpectedXml(): void
    {
        $data = [
            [
                'code' => 'method1',
                'name' => 'Method 1',
                'sandbox' => ['enabled' => true],
                'live' => ['enabled' => false],
                'applicableCountries' => [
                    ['code' => 'US', 'name' => 'United States'],
                    ['code' => 'CA', 'name' => 'Canada'],
                ],
            ],
        ];

        $xml = $this->paymentMethodsUpdater->createXmlByData($data);

        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
        $this->assertEquals('method1', $xml->pointspay_methods->type['id']);
        $this->assertEquals('Method 1', $xml->pointspay_methods->type->label);
    }
    public function testCreateXmlByDataReturnsSomeFieldAsStringXml(): void
    {
        $data = [
            [
                'code' => 'method1',
                'name' => 'Method 1',
                'sandbox' => true,
                'live' => ['enabled' => false],
                'applicableCountries' => [
                    ['code' => 'US', 'name' => 'United States'],
                    ['code' => 'CA', 'name' => 'Canada'],
                ],
            ],
        ];

        $xml = $this->paymentMethodsUpdater->createXmlByData($data);

        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
        $this->assertEquals('method1', $xml->pointspay_methods->type['id']);
        $this->assertEquals('Method 1', $xml->pointspay_methods->type->label);
    }

    public function testFilterContentFiltersOutDisabledMethods(): void
    {
        $contentFromApi = [
            [
                'code' => 'method1',
                'name' => 'Method 1',
                'sandbox' => ['enabled' => true],
                'live' => ['enabled' => false],
                'applicableCountries' => [
                    ['code' => 'US', 'name' => 'United States'],
                    ['code' => 'CA', 'name' => 'Canada'],
                ],
            ],
            [
                'code' => 'method2',
                'name' => 'Method 2',
                'sandbox' => ['enabled' => false],
                'live' => ['enabled' => false],
                'applicableCountries' => [
                    ['code' => 'US', 'name' => 'United States'],
                    ['code' => 'CA', 'name' => 'Canada'],
                ],
            ],
        ];

        $filteredContent = $this->paymentMethodsUpdater->filterContent($contentFromApi);

        $this->assertCount(1, $filteredContent);
        $this->assertEquals('method1', $filteredContent[0]['code']);
    }
    public function testFilterContentIfContentFromApiIsString(): void
    {
        $contentFromApi = 'test string';
        $filteredContent = $this->paymentMethodsUpdater->filterContent($contentFromApi);
        $this->assertEquals($contentFromApi, $filteredContent);
    }
}
