<?php
namespace Pointspay\Pointspay\Test\Service\Api\Environment;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Magento\Framework\HTTP\AsyncClient\GuzzleWrapDeferred;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Api\Data\CheckoutRequestInterface;
use Pointspay\Pointspay\Service\Api\Environment\Live;
use Pointspay\Pointspay\Service\Api\Environment\Sandbox;
use Psr\Http\Message\StreamInterface;

class SandboxTest extends TestCase {
    private $api;

    private $serializer;

    private $live;

    public function testGetPaymentMethodsReturnsExpectedData(): void
    {
        $apiEndpoint = 'https://secure.pointspay.com/';
        $paymentMethodsEndpoint = sprintf('%sapi/v1/payment-methods', $apiEndpoint);
        $methods = ['method1', 'method2'];
        $serializedMethods = json_encode($methods);

        $response = $this
            ->getMockBuilder(\Magento\Framework\HTTP\AsyncClient\Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->method('getBody')->willReturn(json_encode($methods));

        $promise = $this->createMock(GuzzleWrapDeferred::class);
        $promise->method('get')->willReturn($response);

        $this->api->method('getApiEndpoint')->willReturn($apiEndpoint);
        $this->api->method('execute')->with($paymentMethodsEndpoint)->willReturn($promise);
        $this->serializer->method('unserialize')->with($serializedMethods)->willReturn($methods);

        $this->assertEquals($methods, $this->live->getPaymentMethods());
    }

    public function testGetPaymentMethodsThrowsExceptionWhenApiCallFails()
    {
        $apiEndpoint = 'http://example.com';

        $this->api->method('getApiEndpoint')->willReturn($apiEndpoint);
        $this->api->method('execute')->willThrowException(new \Exception());

        $this->expectException(\Exception::class);

        $this->live->getPaymentMethods();
    }

    protected function setUp(): void
    {
        $this->api = $this->createMock(\Pointspay\Pointspay\Service\Api\PaymentMethods\GetMethods::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->live = new Live($this->api, $this->serializer);
    }
}
