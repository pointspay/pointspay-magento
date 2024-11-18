<?php

namespace Pointspay\Pointspay\Api\Checkout;

/**
 * Interface PointspayPaymentMethodsInterface
 *
 * @package Pointspay\Pointspay\Api\Checkout
 */
interface PointspayPaymentMethodsInterface
{
    /**
     * Fetches Pointspay payment methods for logged in customers
     *
     * @param string $cartId
     * @param string $formKey
     * @return mixed[]
     * @api
     */
    public function getAvailablePaymentMethods(string $cartId, string $formKey): array;
}
