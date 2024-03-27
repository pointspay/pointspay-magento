<?php
namespace Pointspay\Pointspay\Test\Service\Api\Checkout;

use Exception;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Api\Checkout\GetPaymentId;
use Pointspay\Pointspay\Service\Api\Checkout\PaymentId;
use Pointspay\Pointspay\Service\Signature\Creator;

class PaymentIdTest extends TestCase
{
    private $api;
    private $signatureCreator;
    private $paymentId;

    protected function setUp(): void
    {
        $this->api = $this->createMock(GetPaymentId::class);
        $this->signatureCreator = $this->createMock(Creator::class);
        $this->paymentId = new PaymentId($this->api, $this->signatureCreator);
    }

    public function testProcessReturnsResponseWhenDataIsValid()
    {
        $data = ['key' => 'value', 'key2' => ['key3' => 'value3']];
        $clientConfig = ['oauth' => ['consumer_key' => 'key', 'nonce' => 'nonce', 'timestamp' => 'timestamp'], 'payment_code' => 'code'];
        $oAuthSignature = 'signature';
        $response = $this->createMock(HttpResponseDeferredInterface::class);

        $this->paymentId->setClientConfig($clientConfig);

        $this->signatureCreator->method('create')->with($data, $clientConfig)->willReturn($oAuthSignature);
        $this->api->expects($this->once())->method('setOAuthSignature')->with($oAuthSignature);
        $this->api->expects($this->once())->method('setOauthConsumerKey')->with($clientConfig['oauth']['consumer_key']);
        $this->api->expects($this->once())->method('setOauthNonce')->with($clientConfig['oauth']['nonce']);
        $this->api->expects($this->once())->method('setOauthTimestamp')->with($clientConfig['oauth']['timestamp']);
        $this->api->method('execute')->willReturn($response);

        $this->assertSame($response, $this->paymentId->process($data));
    }

    public function testProcessThrowsExceptionWhenDataIsInvalid()
    {
        $data = ['invalid_key' => 'invalid_value'];
        $clientConfig = ['oauth' => ['consumer_key' => 'key', 'nonce' => 'nonce', 'timestamp' => 'timestamp'], 'payment_code' => 'code'];

        $this->paymentId->setClientConfig($clientConfig);

        $this->signatureCreator->method('create')->with($data, $clientConfig)->willThrowException(new Exception());

        $this->expectException(Exception::class);

        $this->paymentId->process($data);
    }
}
