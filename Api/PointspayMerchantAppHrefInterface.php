<?php

namespace Pointspay\Pointspay\Api;

interface PointspayMerchantAppHrefInterface
{
    /**
     * @param string $quoteId
     * @return string
     */
    public function getMerchantAppHref($quoteId);
}
