<?php

namespace Pointspay\Pointspay\Model\ResourceModel\FlavourKeys;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Pointspay\Pointspay\Model\FlavourKeys as Model;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'pointspay_keys_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
