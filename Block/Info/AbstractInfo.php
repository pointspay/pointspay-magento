<?php

namespace Pointspay\Pointspay\Block\Info;

use Magento\Framework\View\Element\Template;

class AbstractInfo extends \Magento\Payment\Block\Info
{
    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    /**
     * AbstractInfo constructor.
     *
     * @param \Pointspay\Pointspay\Helper\Config $configHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Pointspay\Pointspay\Helper\Config $configHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }


    public function getSubPaymentTitle($methodCode)
    {
        return $this->configHelper->getSubPaymentTitle($methodCode);
    }
    public function getGeneralPaymentTitle($methodCode)
    {
        return $this->configHelper->getGeneralPaymentTitle($methodCode);
    }
}
