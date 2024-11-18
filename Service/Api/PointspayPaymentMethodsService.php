<?php

namespace Pointspay\Pointspay\Service\Api;

use Pointspay\Pointspay\Api\Checkout\PointspayPaymentMethodsInterface;

/**
 * Class PointspayPaymentMethodsService
 *
 * @package Pointspay\Pointspay\Service\Api
 */
class PointspayPaymentMethodsService implements PointspayPaymentMethodsInterface
{
    /**
     * @var FormValidationPointspayPaymentMethodsService
     */
    private $paymentMethodsService;

    /**
     * PointspayPaymentMethodsService constructor.
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
