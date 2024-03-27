<?php

namespace Pointspay\Pointspay\Test\Service\Api\Environment;

use Exception;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Api\Data\CheckoutRequestInterface;
use Pointspay\Pointspay\Service\Api\Environment\Live;
use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Service\Api\PaymentMethods\GetMethods;

class LiveTest extends TestCase
{
    private $api;
    private $serializer;
    private $live;

    protected function setUp(): void
    {
        $this->api = $this->createMock(GetMethods::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->live = new Live($this->api, $this->serializer);
    }

    public function testGetPaymentMethodsReturnsMethodsWhenApiCallIsSuccessful()
    {
        $apiEndpoint = 'http://example.com';
        $methods = ['method1', 'method2'];
        $serializedMethods = json_encode($methods);

        $this->api->method('getApiEndpoint')->willReturn($apiEndpoint);
        $deffered = $this->createMock(\Magento\Framework\HTTP\AsyncClient\GuzzleWrapDeferred::class);
        $response = $this->createMock(\Magento\Framework\HTTP\AsyncClient\Response::class);
        $response->method('getBody')->willReturn($serializedMethods);
        $deffered->method('get')->willReturn($response);
        $this->api->method('execute')->willReturn($deffered);
        $this->serializer->method('unserialize')->with($serializedMethods)->willReturn($methods);

        $this->assertEquals($methods, $this->live->getPaymentMethods());
    }

    public function testGetPaymentMethodsThrowsExceptionWhenApiCallFails()
    {
        $apiEndpoint = 'http://example.com';

        $this->api->method('getApiEndpoint')->willReturn($apiEndpoint);
        $this->api->method('execute')->willThrowException(new Exception());

        $this->expectException(Exception::class);

        $this->live->getPaymentMethods();
    }
}
