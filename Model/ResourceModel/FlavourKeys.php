<?php

namespace Pointspay\Pointspay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FlavourKeys extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'pointspay_keys_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('pointspay_keys', 'entity_id');
        $this->_useIsObjectNew = true;
    }
}
