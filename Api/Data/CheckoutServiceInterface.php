<?php

namespace Pointspay\Pointspay\Api\Data;

/**
 * Just a marker interface for the checkout service
 * wrapper for CheckoutRequestInterface
 */
interface CheckoutServiceInterface
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function process($data);
}
