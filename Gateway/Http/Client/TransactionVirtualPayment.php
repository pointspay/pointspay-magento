<?php

namespace Pointspay\Pointspay\Gateway\Http\Client;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response;
use Pointspay\Pointspay\Service\Checkout\Service;

class TransactionVirtualPayment implements ClientInterface
{

    /**
     * @var \Pointspay\Pointspay\Service\Checkout\Service
     */
    private $checkoutService;

    /**
     * @var \Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response
     */
    private $responseProcessor;


    /**
     * @param \Pointspay\Pointspay\Service\Checkout\Service $checkoutService
     * @param \Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response $responseProcessor
     */
    public function __construct(
        Service $checkoutService,
        Response $responseProcessor

    ) {
        $this->checkoutService = $checkoutService;
        $this->responseProcessor = $responseProcessor;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array|mixed|string
     * @throws \Exception
     * @throws NoSuchEntityException
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $request = $transferObject->getBody();
        $client = $this->checkoutService->initializeClient();
        $service = $this->checkoutService->createCheckoutService($client, $transferObject->getClientConfig());
        $this->checkoutService->logRequest('Retrieving payment_id', $request);
        try {

            $responseObject = $service->processVirtual($request);
            $response = $this->responseProcessor->process($request + $transferObject->getClientConfig(), $responseObject);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['error_details']['request'] =$request + $transferObject->getClientConfig() ;
            $response['error_details']['response'] = dump($responseObject, true);
        }
       // throw new Exception($response['error']);
        $this->checkoutService->logResponse('Checkout Service response',$response);

        return $response;
    }
}
