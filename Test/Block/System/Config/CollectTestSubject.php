<?php

namespace Pointspay\Pointspay\Test\Block\System\Config;

class CollectTestSubject extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    public function getName()
    {
        return 'merchant_certificate';
    }
    public function getHtmlId()
    {
        return 'pointspay_access_settings_merchant_certificate';
    }
    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
      return 'some_html_content';

    }

}
