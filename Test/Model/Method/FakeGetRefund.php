<?php

namespace Pointspay\Pointspay\Test\Model\Method;

use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Helper\Config;
use Psr\Log\LoggerInterface;

class FakeGetRefund extends \Pointspay\Pointspay\Service\Api\Refund\GetRefund
{
    public function __construct(
        Config $generalHelper,
        SerializerInterface $serializer,
        AsyncClientInterface $asyncClient,
        LoggerInterface $logger,
        \Pointspay\Pointspay\Test\Model\Method\FakeConfig $fakeGeneralConfig
    )
    {
        parent::__construct($fakeGeneralConfig, $serializer, $asyncClient, $logger);
        $this->generalHelper = $fakeGeneralConfig;
    }
    public function getApiEndpoint($code = null)
    {
        return \Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface::POINTSPAY_SANDBOX_URL;
    }

}
