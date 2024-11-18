<?php

namespace Pointspay\Pointspay\Api\Checkout;

/**
 * Interface GuestPointspayPaymentMethodsInterface
 *
 * @package Pointspay\Pointspay\Api\Checkout
 */
interface GuestPointspayPaymentMethodsInterface
{
    /**
     * Fetches Pointspay payment methods for guest customers
     *
     * @param string $cartId
     * @param string $formKey
     * @return mixed[]
     * @api
     */
    public function getAvailablePaymentMethods(string $cartId, string $formKey): array;
}
