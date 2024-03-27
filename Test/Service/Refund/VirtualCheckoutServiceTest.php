<?php
namespace Pointspay\Pointspay\Test\Service\Refund;

use Exception;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Api\Refund\Refund;
use Pointspay\Pointspay\Service\Refund\VirtualRefundService;

class VirtualCheckoutServiceTest extends TestCase
{
    private $refundService;
    private $virtualCheckoutService;

    protected function setUp(): void
    {
        $this->refundService = $this->createMock(Refund::class);
        $this->virtualCheckoutService = new VirtualRefundService($this->refundService);
    }

    public function testProcessVirtualReturnsResponseWhenRequestIsValid()
    {
        $request = ['key' => 'value'];
        $response = ['response_key' => 'response_value'];

        $this->refundService->method('process')->with($request)->willReturn($response);

        $this->assertSame($response, $this->virtualCheckoutService->processVirtual($request));
    }

    public function testProcessVirtualThrowsExceptionWhenRequestIsInvalid()
    {
        $request = ['invalid_key' => 'invalid_value'];

        $this->refundService->method('process')->with($request)->willThrowException(new Exception());

        $this->expectException(Exception::class);

        $this->virtualCheckoutService->processVirtual($request);
    }
}
