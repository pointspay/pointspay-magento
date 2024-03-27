<?php
namespace Pointspay\Pointspay\Test\Service\Api\Checkout;

use GuzzleHttp\Exception\TransferException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Api\Checkout\GetPaymentId;
use Pointspay\Pointspay\Helper\Config;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Service\HTTP\AsyncClient\GuzzleAsyncClient;
use Pointspay\Pointspay\Service\Logger\Logger;
use Psr\Log\LoggerInterface;

class GetPaymentIdTest extends TestCase
{
    private $generalHelper;
    private $serializer;
    private $asyncClient;
    private $logger;
    private $getPaymentId;

    protected function setUp(): void
    {
        $this->generalHelper = $this->createMock(Config::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->asyncClient = $this->createMock(GuzzleAsyncClient::class);
        $this->logger = $this->createMock(Logger::class);

        $this->getPaymentId = new GetPaymentId(
            $this->generalHelper,
            $this->serializer,
            $this->asyncClient,
            $this->logger
        );
    }

    public function testExecuteReturnsResponseWhenRequestIsValid()
    {
        $apiEndpoint = 'http://example.com';
        $method = 'POST';
        $arrayForApi = ['key' => 'value'];
        $headersForApi = ['Content-Type' => 'application/json'];

        $this->asyncClient->method('request')->willReturn($this->createMock(HttpResponseDeferredInterface::class));

        $this->assertInstanceOf(HttpResponseDeferredInterface::class, $this->getPaymentId->execute($apiEndpoint, $method, $arrayForApi, $headersForApi));
    }

    public function testExecuteLogsErrorWhenTransferExceptionOccurs()
    {
        $apiEndpoint = 'http://example.com';
        $method = 'POST';
        $arrayForApi = ['key' => 'value'];
        $headersForApi = ['Content-Type' => 'application/json'];

        $this->asyncClient->method('request')->willThrowException(new TransferException());

        $this->logger->expects($this->any())->method('error');

        $this->assertNull($this->getPaymentId->execute($apiEndpoint, $method, $arrayForApi, $headersForApi));
    }

    public function testSuccessfulCurlRequestReturnsExpectedResult()
    {
        $apiEndpoint = 'http://example.com';
        $body = 'test_body';
        $headersForApi = ['Content-Type' => 'application/json'];


        $result = $this->getPaymentId->makeCurlRequest($apiEndpoint, $body, $headersForApi);
        $containsResult = strpos($result, 'Example Domain') !== false;
        $this->assertTrue($containsResult);
    }
}
