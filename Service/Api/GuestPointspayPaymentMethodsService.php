<?php

namespace Pointspay\Pointspay\Service\Api;

use Pointspay\Pointspay\Api\Checkout\GuestPointspayPaymentMethodsInterface;

class GuestPointspayPaymentMethodsService implements GuestPointspayPaymentMethodsInterface
{
    /**
     * @var FormValidationPointspayPaymentMethodsService
     */
    private $paymentMethodsService;

    /**
     * GuestPointspayPaymentMethodsService constructor.
     * @param FormValidationPointspayPaymentMethodsService $paymentMethodsService
     */
    public function __construct(FormValidationPointspayPaymentMethodsService $paymentMethodsService)
    {
        $this->paymentMethodsService = $paymentMethodsService;
    }

    public function getAvailablePaymentMethods(string $cartId, string $formKey): array
    {
        return $this->paymentMethodsService->getAvailablePaymentMethods($cartId, $formKey);
    }
}
