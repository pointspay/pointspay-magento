<?php
namespace Pointspay\Pointspay\Test\Service\Api\Refund;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Api\Data\CheckoutRequestInterface;
use Pointspay\Pointspay\Service\Api\Refund\Refund;
use Pointspay\Pointspay\Service\Signature\Creator;

class RefundTest extends TestCase
{
    private $checkoutRequestInterfaceMock;
    private $signatureCreatorMock;
    private $refund;

    protected function setUp(): void
    {
        $this->checkoutRequestInterfaceMock = $this->createMock(\Pointspay\Pointspay\Service\Api\Refund\GetRefund::class);
        $this->signatureCreatorMock = $this->createMock(Creator::class);
        $this->refund = new Refund($this->checkoutRequestInterfaceMock, $this->signatureCreatorMock);
    }

    public function testProcessWithValidData()
    {
        $data = ['test' => 'data'];
        $clientConfig = ['oauth' => ['consumer_key' => 'key', 'nonce' => 'nonce', 'timestamp' => 'timestamp'], 'payment_code' => 'code'];
        $this->refund->setClientConfig($clientConfig);
        $this->signatureCreatorMock->expects($this->once())->method('create')->willReturn('signature');
        $this->checkoutRequestInterfaceMock->expects($this->once())->method('execute')->willReturn('response');

        $response = $this->refund->process($data);

        $this->assertEquals('response', $response);
    }

    public function testProcessWithEmptyData()
    {
        $data = [];
        $clientConfig = ['oauth' => ['consumer_key' => 'key', 'nonce' => 'nonce', 'timestamp' => 'timestamp'], 'payment_code' => 'code'];
        $this->refund->setClientConfig($clientConfig);
        $this->signatureCreatorMock->expects($this->once())->method('create')->willReturn('signature');
        $this->checkoutRequestInterfaceMock->expects($this->once())->method('execute')->willReturn('response');

        $response = $this->refund->process($data);

        $this->assertEquals('response', $response);
    }

    public function testSetAndGetClientConfig()
    {
        $clientConfig = ['test' => 'data'];

        $this->refund->setClientConfig($clientConfig);

        $this->assertEquals($clientConfig, $this->refund->getClientConfig());
    }
}
