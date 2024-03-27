<?php

namespace Pointspay\Pointspay\Service\Logger;

use Magento\Store\Model\StoreManagerInterface;
use Monolog\Logger as MonologLogger;
use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;
use Pointspay\Pointspay\Helper\Config;

class Logger extends MonologLogger
{

    const REQUEST = 201;

    const RESULT = 202;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     * Overrule the default to add PP specific loggers to log into seperate files
     *
     * @var array $levels Logging levels
     */
    protected static $levels = [
        100 => 'DEBUG',
        200 => 'INFO',
        201 => 'REQUEST',
        202 => 'RESULT',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    ];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $config;

    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config,
        $name,
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @param $message
     * @param array $context
     * @param $code
     * @return bool|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addDebug($message, array $context = [], $code = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if ($this->config->getDebugMode($code, $storeId)) {
            return $this->addRecord(static::DEBUG, $message, $context);
        }
        return false;
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
        $storeId = $this->storeManager->getStore()->getId();
        if ($this->config->getDebugMode($code, $storeId)) {
            return $this->addRecord(static::WARNING, $message, $context);
        }
        return false;
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
        return $this->addRecord(static::CRITICAL, $message, $context);
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
        return $this->addRecord(static::ERROR, $message, $context);
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
        $storeId = $this->storeManager->getStore()->getId();
        if ($this->config->getDebugMode($code, $storeId)) {
            return $this->addRecord(static::RESULT, $message, $context);
        }
        return false;
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
        $storeId = $this->storeManager->getStore()->getId();
        if ($this->config->getDebugMode($code, $storeId)) {
            return $this->addRecord(static::REQUEST, $message, $context);
        }
        return false;
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
        $storeId = $this->storeManager->getStore()->getId();
        if ($this->config->getDebugMode($code, $storeId)) {
            return $this->addRecord(static::INFO, $message, $context);
        }
    }

}
