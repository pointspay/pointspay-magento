<?php

namespace Pointspay\Pointspay\Api;

interface GuestPointspayMerchantAppHrefInterface
{
    /**
     * @param string $cartId
     * @return string
     */
    public function getMerchantAppHref($cartId);
}
