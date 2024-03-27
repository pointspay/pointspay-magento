<?php

namespace Pointspay\Pointspay\Block\Checkout\LayoutProcessor;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Framework\Stdlib\ArrayManager;
use Pointspay\Pointspay\Helper\Config;

class BillingAddressClonerProcessor
{
    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager
     * @param \Pointspay\Pointspay\Helper\Config $config
     */
    public function __construct(
        ArrayManager $arrayManager,
        Config $config
    ) {
        $this->arrayManager = $arrayManager;
        $this->config = $config;
    }

    public function beforeProcess(LayoutProcessor $processor, $jsLayout)
    {
        $pathExist = $this->arrayManager->exists(
            'components/checkout/children/steps/children/billing-step/children/payment/children/renders/children/pointspay',
            $jsLayout
        );
        if (!$pathExist) {
            return [$jsLayout];
        }
        $pointspayOriginalArray = $this->arrayManager->get(
            'components/checkout/children/steps/children/billing-step/children/payment/children/renders/children/pointspay/methods/pointspay_required_settings',
            $jsLayout
        );
        foreach ($this->config->getEnabledPaymentMethodsDetails() as $methodsDetail){
            $jsLayout = $this->arrayManager->set(
                'components/checkout/children/steps/children/billing-step/children/payment/children/renders/children/pointspay/methods/' . $methodsDetail['code'],
                $jsLayout,
                $pointspayOriginalArray
            );
        }
        return [$jsLayout];
    }
}
