<?php

namespace Pointspay\Pointspay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Pointspay\Pointspay\Helper\Config;
use Psr\Log\LoggerInterface;

class PointspayVirtualConfigProvider implements ConfigProviderInterface
{
    const CODE = \Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS;

    const HREF = 'href';
    const CREATED_AT = 'created_at';

    const ORDER_ID = 'order_id';
    const PAYMENT_ID = 'payment_id';
    const STATUS = 'status';
    const STATUS_MESSAGE = 'status_message';
    const STATUS_CODE = 'code';
    const MESSAGE = 'message';
    const KEY = 'key';

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Config $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $payment = [
            'pointspay_main_payment_method_code'=> self::CODE
        ];

        return ['payment' => $payment];
    }

}
