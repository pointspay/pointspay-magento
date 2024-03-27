<?php

namespace Pointspay\Pointspay\Test\Service\HTTP\AsyncClient;

use PHPUnit\Framework\TestCase;
use Magento\Framework\HTTP\AsyncClient\Request as MagentoRequest;
use Pointspay\Pointspay\Service\HTTP\AsyncClient\Request;

class RequestTest extends TestCase
{
    private $request;

    protected function setUp(): void
    {
        $this->request = new Request('http://example.com', 'GET', [], null, ['timeout' => 30]);
    }

    public function testRequestInheritsMagentoRequest()
    {
        $this->assertInstanceOf(MagentoRequest::class, $this->request);
    }

    public function testRequestStoresOptions()
    {
        $options = $this->request->getOptions();
        $this->assertIsArray($options);
        $this->assertArrayHasKey('timeout', $options);
        $this->assertEquals(30, $options['timeout']);
    }

    public function testRequestReturnsEmptyOptionsIfNotSet()
    {
        $requestWithoutOptions = new Request('http://example.com', 'GET', [], null);
        $this->assertEmpty($requestWithoutOptions->getOptions());
    }
}
