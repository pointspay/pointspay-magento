<?php

namespace Pointspay\Pointspay\Test\Service\Signature\Validator;

use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class ParseTest extends TestCase
{
    private $parse;

    protected function setUp(): void
    {
        $this->parse = new \Pointspay\Pointspay\Service\Signature\Validator\Parse();
    }

    public function testParseRemovesOauthFromStartOfString()
    {
        $result = $this->parse->parse('Oauth key="value"');
        $this->assertEquals(['key' => 'value'], $result->getData());
    }

    public function testParseSplitsStringIntoKeyValuePairs()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->parse->parse('key1="value1", key2="value2"');
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $result->getData());
    }

    public function testParseTrimsQuotesFromValues()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->parse->parse('key="value"');
        $this->assertEquals(['key' => 'value'], $result->getData());
    }

    public function testParseReturnsEmptyObjectForEmptyString()
    {
        $result = $this->parse->parse('');
        $this->assertEquals([], $result->getData());
    }

    public function testParseThrowsExceptionForInvalidString()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->parse->parse('invalid');
    }
}
