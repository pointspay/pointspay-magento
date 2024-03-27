<?php
namespace Pointspay\Pointspay\Test\Service\Api\PaymentMethods;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Api\PaymentMethods\GetMethods;
use Pointspay\Pointspay\Service\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use GuzzleHttp\Exception\TransferException;

class GetMethodsTest extends TestCase
{
    private $getMethods;
    private $asyncClient;
    private $generalHelper;
    private $serializer;
    private $logger;

    protected function setUp(): void
    {
        $this->asyncClient = $this->createMock(\Magento\Framework\HTTP\AsyncClientInterface::class);
        $this->generalHelper = $this->createMock(\Pointspay\Pointspay\Helper\Config::class);
        $this->serializer = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->logger = $this->createMock(\Pointspay\Pointspay\Service\Logger\Logger::class);

        $this->getMethods = new GetMethods(
            $this->generalHelper,
            $this->serializer,
            $this->asyncClient,
            $this->logger
        );
    }

    public function testExecuteReturnsHttpResponseDeferredInterfaceOnSuccess()
    {
        $this->serializer->method('serialize')->willReturn('{}');
        $this->generalHelper->method('getApiKey')->willReturn('test_api_key');
        $this->generalHelper->method('getRequestTimeout')->willReturn(30);
        $apiEndpoint = 'http://example.com';
        $this->generalHelper->method('getApiEndpoint')->willReturn($apiEndpoint);
        $this->asyncClient->method('request')->willReturn($this->createMock(HttpResponseDeferredInterface::class));


        $result = $this->getMethods->execute();

        $this->assertInstanceOf(HttpResponseDeferredInterface::class, $result);
    }

    public function testExecuteLogsErrorOnTransferException()
    {
        $this->serializer->method('serialize')->willReturn('{}');
        $this->generalHelper->method('getApiKey')->willReturn('test_api_key');
        $apiEndpoint = 'http://example.com';
        $this->generalHelper->method('getApiEndpoint')->willReturn($apiEndpoint);
        $this->generalHelper->method('getRequestTimeout')->willReturn(30);
        $this->asyncClient->method('request')->willThrowException(new TransferException());

        $this->logger->expects($this->any())->method('addError');

        $this->getMethods->execute();
    }
}
