<?php

namespace Pointspay\Pointspay\Block\Payment\Block\Form;

use Magento\Payment\Block\Form\Container;
use Pointspay\Pointspay\Helper\Config;

class ContainerPlugin
{
    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    public function __construct(
        Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * @param Container $subject
     * @param array $result
     * @return array
     */
    public function afterGetMethods(Container $subject, array $result): array
    {
        $pointspayGeneralPaymentAlreadyProcessed = false;
        $allPointspayEnabledMethods = $this->configHelper->getEnabledPaymentMethodsDetails();
        foreach ($result as $key => $method) {
            foreach ($allPointspayEnabledMethods as $methodKey => &$pointspayEnabledMethod) {
                if (!$pointspayGeneralPaymentAlreadyProcessed) {
                    if ($method->getCode() === \Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS) {
                        $pointspayGeneralPaymentAlreadyProcessed = true;
                        continue;
                    }
                }
                if ($method->getCode() === \Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS) {
                    (function ($pointspayEnabledMethod) {
                        $this->code = $pointspayEnabledMethod['pointspay_code'];
                    })->call($method, $pointspayEnabledMethod);
                    unset($allPointspayEnabledMethods[$methodKey]);
                }
            }
        }
        $subject->setData('methods', $result);
        return $result;
    }
}
