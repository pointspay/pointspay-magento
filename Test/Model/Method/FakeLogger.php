<?php

namespace Pointspay\Pointspay\Test\Model\Method;

use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;

class FakeLogger extends \Pointspay\Pointspay\Service\Logger\Logger
{
    /**
     * @param $message
     * @param array $context
     * @param $code
     * @return bool|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addDebug($message, array $context = [], $code = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS)
    {
        return true;
    }

    /**
     * @param $message
     * @param array $context
     * @param $code
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addWarning($message, array $context = [], $code = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS): bool
    {
        return true;
    }

    /**
     * Intentionally log everything in this method bypassing the debug mode settings
     *
     * @param $message
     * @param array $context
     * @param $code
     * @return bool
     */
    public function addCritical($message, array $context = [], $code = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS): bool
    {
        return true;
    }

    /**
     * Intentionally log everything in this method bypassing the debug mode settings
     *
     * @param $message
     * @param array $context
     * @param $code
     * @return bool
     */
    public function addError($message, array $context = [], $code = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS): bool
    {
        return true;
    }

    /**
     * @param $message
     * @param array $context
     * @param $code
     * @return bool|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addResult($message, array $context = [], $code = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS)
    {
        return true;
    }

    /**
     * @param $message
     * @param array $context
     * @param string $code
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addRequest($message, array $context = [], $code = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS)
    {
        return true;
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @param string $code
     * @return Boolean Whether the record has been processed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addInfo($message, array $context = [], $code = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS)
    {
       return true;
    }
}
