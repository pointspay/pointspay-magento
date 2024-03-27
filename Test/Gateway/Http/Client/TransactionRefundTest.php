<?php
namespace Pointspay\Pointspay\Test\Gateway\Http\Client;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Gateway\Http\Client\TransactionRefund;
use Magento\Payment\Gateway\Http\TransferInterface;
use Pointspay\Pointspay\Service\Refund\Service;
use Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response;
class TransactionRefundTest extends TestCase {
    public function testPlaceRequestHandlesSuccessfulRefund()
    {
        $transferObject = $this->createMock(TransferInterface::class);
        $transferObject->method('getBody')->willReturn(['some' => 'data']);
        $transferObject->method('getClientConfig')->willReturn(['config' => 'data']);

        $checkoutServiceClient = $this->createMock(\Pointspay\Pointspay\Service\Refund\VirtualRefundService::class);
        $checkoutApiServiceClient = $this->createMock(\Pointspay\Pointspay\Service\Api\Refund\Refund::class);
        $checkoutService = $this->createMock(Service::class);
        $checkoutService->method('initializeClient')->willReturn($checkoutApiServiceClient);
        $checkoutService->method('createCheckoutService')->willReturn($checkoutServiceClient);
        $checkoutService->expects($this->once())->method('logRequest');

        $responseProcessor = $this->createMock(Response::class);
        $responseProcessor->method('process')->willReturn(['response' => 'data']);

        $transactionRefund = new TransactionRefund($checkoutService, $responseProcessor);
        $response = $transactionRefund->placeRequest($transferObject);

        $this->assertArrayHasKey('response', $response);
    }

    public function testPlaceRequestHandlesFailedRefund()
    {
        $transferObject = $this->createMock(TransferInterface::class);
        $transferObject->method('getBody')->willReturn(['some' => 'data']);
        $transferObject->method('getClientConfig')->willReturn(['config' => 'data']);

        $checkoutServiceClient = $this->createMock(\Pointspay\Pointspay\Service\Refund\VirtualRefundService::class);
        $checkoutApiServiceClient = $this->createMock(\Pointspay\Pointspay\Service\Api\Refund\Refund::class);
        $checkoutService = $this->createMock(Service::class);
        $checkoutService->method('initializeClient')->willReturn($checkoutApiServiceClient);
        $checkoutService->method('createCheckoutService')->willReturn($checkoutServiceClient);
        $checkoutService->expects($this->once())->method('logRequest');

        $responseProcessor = $this->createMock(Response::class);
        $responseProcessor->method('process')->willThrowException(new \Exception('Error'));

        $transactionRefund = new TransactionRefund($checkoutService, $responseProcessor);
        $response = $transactionRefund->placeRequest($transferObject);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Error', $response['error']);
    }
}
