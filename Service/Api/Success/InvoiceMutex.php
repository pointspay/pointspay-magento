<?php
declare(strict_types=1);

namespace Pointspay\Pointspay\Service\Api\Success;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\Lock\LockManagerInterface;
use Pointspay\Pointspay\Api\InvoiceMutexInterface;

/**
 * @inheritdoc
 */
class InvoiceMutex implements InvoiceMutexInterface
{
    private const LOCK_PREFIX = 'invoice_lock_';

    private const LOCK_TIMEOUT = 120;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var int
     */
    private $lockWaitTimeout;

    /**
     * @param LockManagerInterface $lockManager
     * @param int $lockWaitTimeout
     */
    public function __construct(
        LockManagerInterface $lockManager,
        int $lockWaitTimeout = self::LOCK_TIMEOUT
    ) {
        $this->lockManager = $lockManager;
        $this->lockWaitTimeout = $lockWaitTimeout;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $orderIncrementId, callable $callable, array $args = [])
    {
        if (empty($orderIncrementId)) {
            throw new \InvalidArgumentException('Order increment ID must be provided');
        }

        if ($this->lockManager->isLocked(self::LOCK_PREFIX . $orderIncrementId)) {
            return false;
        }

        if ($this->lockManager->lock(self::LOCK_PREFIX . $orderIncrementId, $this->lockWaitTimeout)) {
            try {
                return $callable(...$args);
            } finally {
                $this->lockManager->unlock(self::LOCK_PREFIX . $orderIncrementId);
            }
        } else {
            throw new LocalizedException(
                __('Could not acquire lock for the order increment ID: %1', $orderIncrementId)
            );
        }
    }
}
