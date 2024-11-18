<?php

namespace Pointspay\Pointspay\Service\Api\CartProvider;

use Magento\Quote\Model\Quote;

/**
 * Interface CartProvider
 *
 * @package Pointspay\Pointspay\Service\Api\CartProvider
 */
interface CartProvider
{
    public function getQuote(string $cartId): Quote;
}
