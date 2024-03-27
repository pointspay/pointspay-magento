<?php

namespace Pointspay\Pointspay\Test\Service\Api;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Api\AbstractApi;

class AbstractApiTest extends TestCase
{
    private $abstractApi;

    protected function setUp(): void
    {
        $this->abstractApi = $this->getMockBuilder(AbstractApi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute','getOauthParamsHeader', 'getOauthParamsBody', 'getOauthConsumerKey', 'getOauthSignatureMethod', 'getOauthNonce', 'getOauthTimestamp', 'getOauthSignature', 'getApiEndpoint', 'setOauthConsumerKey', 'setOauthNonce', 'setOAuthTimestamp', 'setOAuthSignature', 'logError'])
            ->getMock();
    }

    public function oauthParamsHeaderReturnsExpectedFormat()
    {
        $this->abstractApi->method('getOauthConsumerKey')->willReturn('consumerKey');
        $this->abstractApi->method('getOauthSignatureMethod')->willReturn('SHA256withRSA');
        $this->abstractApi->method('getOauthNonce')->willReturn('nonce');
        $this->abstractApi->method('getOauthTimestamp')->willReturn('timestamp');
        $this->abstractApi->method('getOauthSignature')->willReturn('signature');

        $expected = 'oauth_consumer_key="consumerKey",oauth_signature_method="SHA256withRSA",oauth_nonce="nonce",oauth_timestamp="timestamp",oauth_signature="signature"';
        $this->assertEquals($expected, $this->abstractApi->getOauthParamsHeader());
    }

    public function oauthParamsBodyReturnsExpectedFormat()
    {
        $this->abstractApi->method('getOauthConsumerKey')->willReturn('consumerKey');
        $this->abstractApi->method('getOauthSignatureMethod')->willReturn('SHA256withRSA');
        $this->abstractApi->method('getOauthNonce')->willReturn('nonce');
        $this->abstractApi->method('getOauthTimestamp')->willReturn('timestamp');

        $expected = 'consumerKeySHA256withRSAnoncetimestamp';
        $this->assertEquals($expected, $this->abstractApi->getOauthParamsBody());
    }

    public function testGetOauthConsumerKey()
    {
        $this->abstractApi->method('getOauthConsumerKey')->willReturn('testKey');
        $this->assertEquals('testKey', $this->abstractApi->getOauthConsumerKey());
    }

    public function testGetOauthNonce()
    {
        $this->abstractApi->method('getOauthNonce')->willReturn('testNonce');
        $this->assertEquals('testNonce', $this->abstractApi->getOauthNonce());
    }

    public function testGetOAuthTimestamp()
    {
        $this->abstractApi->method('getOauthTimestamp')->willReturn('testTimestamp');
        $this->assertEquals('testTimestamp', $this->abstractApi->getOauthTimestamp());
    }

    public function testGetOAuthSignature()
    {
        $this->abstractApi->method('getOauthSignature')->willReturn('testSignature');
        $this->assertEquals('testSignature', $this->abstractApi->getOauthSignature());
    }
    public function testSetOauthConsumerKey()
    {
        $this->abstractApi->expects($this->any())->method('setOauthConsumerKey');
        $this->abstractApi->expects($this->any())->method('getOauthConsumerKey')->willReturn('test');
        $this->abstractApi->setOauthConsumerKey('test');
        $this->assertEquals('test', $this->abstractApi->getOauthConsumerKey());
    }

    public function testSetOauthNonce()
    {
        $this->abstractApi->expects($this->any())->method('setOauthNonce');
        $this->abstractApi->expects($this->any())->method('getOauthNonce')->willReturn('test');
        $this->abstractApi->setOauthNonce('test');
        $this->assertEquals('test', $this->abstractApi->getOauthNonce());
    }

    public function testSetOAuthTimestamp()
    {
        $this->abstractApi->expects($this->any())->method('setOauthTimestamp');
        $this->abstractApi->expects($this->any())->method('getOauthTimestamp')->willReturn('test');
        $this->abstractApi->setOauthTimestamp('test');
        $this->assertEquals('test', $this->abstractApi->getOauthTimestamp());
    }

    public function testSetOAuthSignature()
    {
        $this->abstractApi->expects($this->any())->method('setOauthSignature');
        $this->abstractApi->expects($this->any())->method('getOauthSignature')->willReturn('test');
        $this->abstractApi->setOauthSignature('test');
        $this->assertEquals('test', $this->abstractApi->getOauthSignature());
    }
}
