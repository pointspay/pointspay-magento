<?php

namespace Pointspay\Pointspay\Test\Model\Method;

use Pointspay\Pointspay\Test\Model\Method\FakeRefund as CheckoutServiceInterface;
use Pointspay\Pointspay\Service\Refund\VirtualRefundService;
use Pointspay\Pointspay\Test\MageObjectManager;

class FakeVirtualRefundService extends VirtualRefundService
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
        \Pointspay\Pointspay\Api\Data\CheckoutServiceInterface $checkoutService,
        array $clientConfig = []
    ) {
        $this->checkoutService = $checkoutService;
        $this->checkoutService->setClientConfig($clientConfig);
        parent::__construct($checkoutService, $clientConfig);
    }

    public function processVirtual(array $request)
    {
        $realObjectManager = new MageObjectManager();
        $fakeApi = $realObjectManager->objectManager->create(FakeGetRefund::class);
        $oldConfig =  $this->checkoutService->getClientConfig();
        $this->checkoutService = $realObjectManager->objectManager->create(FakeRefund::class, ['api' => $fakeApi]);
        $this->checkoutService->setClientConfig($oldConfig);
        $response = $this->checkoutService->process($request);
        return $response;
    }
}
