<?php
namespace Pointspay\Pointspay\Test\Service\Checkout;

use Exception;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Api\Checkout\PaymentId;
use Pointspay\Pointspay\Service\Checkout\VirtualCheckoutService;

class VirtualCheckoutServiceTest extends TestCase
{
    private $paymentIdService;
    private $virtualCheckoutService;

    protected function setUp(): void
    {
        $this->paymentIdService = $this->createMock(PaymentId::class);
        $this->virtualCheckoutService = new VirtualCheckoutService($this->paymentIdService);
    }

    public function testProcessVirtualReturnsResponseWhenRequestIsValid()
    {
        $request = ['key' => 'value'];
        $response = ['response_key' => 'response_value'];

        $this->paymentIdService->method('process')->with($request)->willReturn($response);

        $this->assertSame($response, $this->virtualCheckoutService->processVirtual($request));
    }

    public function testProcessVirtualThrowsExceptionWhenRequestIsInvalid()
    {
        $request = ['invalid_key' => 'invalid_value'];

        $this->paymentIdService->method('process')->with($request)->willThrowException(new Exception());

        $this->expectException(Exception::class);

        $this->virtualCheckoutService->processVirtual($request);
    }
}
