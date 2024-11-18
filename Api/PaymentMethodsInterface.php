<?php

namespace Pointspay\Pointspay\Api;

/**
 * Interface PaymentMethodsInterface
 *
 * Defines a contract for retrieving available payment methods.
 */
interface PaymentMethodsInterface
{
    /**
     * Retrieves the display title for a given payment flavor code.
     *
     * @param string $flavorCode The unique code representing a specific Pointspay payment flavor.
     * @return string The title/name of the payment method, or "Unknown Payment Method" if not found.
     */
    public function getPaymentTitleByCode($flavorCode);
}
