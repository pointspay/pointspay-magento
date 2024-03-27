<?php

namespace Pointspay\Pointspay\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\TransferInterface;
use Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response;
use Pointspay\Pointspay\Service\Refund\Service;

class TransactionRefund implements TransactionRefundInterface
{
    /**
     * @var \Pointspay\Pointspay\Service\Refund\Service
     */
    private $checkoutService;

    /**
     * @var \Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response
     */
    private $responseProcessor;


    /**
     * @param \Pointspay\Pointspay\Service\Refund\Service $checkoutService
     * @param \Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response $responseProcessor
     */
    public function __construct(
        Service $checkoutService,
        Response $responseProcessor
    ) {
        $this->checkoutService = $checkoutService;
        $this->responseProcessor = $responseProcessor;
    }

    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        $client = $this->checkoutService->initializeClient();
        $service = $this->checkoutService->createCheckoutService($client, $transferObject->getClientConfig());
        $this->checkoutService->logRequest('Processing Refund', $request);
        try {
            $responseObject = $service->processVirtual($request);
            $response = $this->responseProcessor->process($request + $transferObject->getClientConfig(), $responseObject);
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        $this->checkoutService->logResponse('Refund Service response',$response);

        return $response;
    }
}
