<?php

namespace Pointspay\Pointspay\Service\Refund;

use Pointspay\Pointspay\Api\Data\CheckoutServiceInterface;

class VirtualRefundService
{
    /**
     * @var \Pointspay\Pointspay\Api\Data\CheckoutServiceInterface
     */
    private $checkoutService;

    /**
     * @param CheckoutServiceInterface $checkoutService
     * @param array $clientConfig
     */
    public function __construct(
        CheckoutServiceInterface $checkoutService,
        array $clientConfig = []
    )
    {
        $this->checkoutService = $checkoutService;
        $this->checkoutService->setClientConfig($clientConfig);
    }

    public function processVirtual(array $request)
    {
        $response = $this->checkoutService->process($request);
        return $response;
    }
}
