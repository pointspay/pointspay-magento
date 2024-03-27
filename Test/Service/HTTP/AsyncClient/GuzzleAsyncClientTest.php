<?php

namespace Pointspay\Pointspay\Test\Service\HTTP\AsyncClient;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Promise\FulfilledPromise;
use Pointspay\Pointspay\Service\HTTP\AsyncClient\GuzzleAsyncClient;
use Pointspay\Pointspay\Service\HTTP\AsyncClient\Request as Request;

class GuzzleAsyncClientTest extends TestCase
{
    private $client;
    private $guzzleClient;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->guzzleClient = new GuzzleAsyncClient($this->client);
    }

    public function testGuzzleAsyncClientMakesRequestGet()
    {
        $request = new Request('http://example.com', 'GET', [], null, ['timeout' => 30]);
        $response = new Response(200, [], 'OK');
        $promise = new FulfilledPromise($response);

        $this->client->expects($this->any())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('http://example.com'),
                $this->equalTo(['timeout' => 30, 'headers' => []])
            )
            ->willReturn($promise);

        $deferredResponse = $this->guzzleClient->request($request);

        $this->assertEquals(200, $deferredResponse->get()->getStatusCode());
        $this->assertEquals('OK', $deferredResponse->get()->getBody());
    }
    public function testGuzzleAsyncClientMakesRequestPost()
    {
        $request = new Request('http://example.com', 'POST', [], 'body', ['timeout' => 30]);
        $response = new Response(200, [], 'OK');
        $promise = new FulfilledPromise($response);

        $this->client->expects($this->any())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('http://example.com'),
                $this->equalTo(['timeout' => 30, 'headers' => [], 'body' => 'body'])
            )
            ->willReturn($promise);

        $deferredResponse = $this->guzzleClient->request($request);

        $this->assertEquals(200, $deferredResponse->get()->getStatusCode());
        $this->assertEquals('OK', $deferredResponse->get()->getBody());
    }

}
