<?php

namespace Pointspay\Pointspay\Block\Adminhtml\System\Config;

/**
 * Class CustomCloneModel
 *
 * This class is a custom clone model for configuration fields in the Magento admin panel.
 */
class CustomCloneModel extends \Magento\Framework\App\Config\Value
{
    /**
     * Returns an array of prefixes
     *
     * @return array
     */
    public function getPrefixes()
    {
        return [];
    }
}
