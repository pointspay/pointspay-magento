<?php

namespace Pointspay\Pointspay\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pointspay\Pointspay\Helper\Config;

class Version extends Field
{
    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    public function __construct(
        Config $configHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * Retrieve the setup version of the extension
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->configHelper->getModuleVersion();
    }
}
