<?php

namespace Pointspay\Pointspay\Service\Checkout;

use Pointspay\Pointspay\Api\Data\CheckoutServiceInterface;


class VirtualCheckoutService
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
