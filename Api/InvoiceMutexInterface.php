<?php

namespace Pointspay\Pointspay\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Intended to prevent race conditions during order place operation by concurrent requests(IPN or Sale method).
 */
interface InvoiceMutexInterface
{

    /**
     * Acquires a lock for invoice creation, executes callable and releases the lock after.
     *
     * @param string $orderIncrementId
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws LocalizedException
     */
    public function execute(string $orderIncrementId, callable $callable, array $args = []);
}
