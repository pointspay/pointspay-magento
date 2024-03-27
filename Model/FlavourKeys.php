<?php

namespace Pointspay\Pointspay\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys as ResourceModel;

class FlavourKeys extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'pointspay_keys_model';


    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function setCertificate($value)
    {
        $this->setData('certificate', base64_encode($value));
    }

    public function getCertificate()
    {
        return base64_decode($this->getData('certificate'));
    }

    public function setPrivateKey($value)
    {
        $this->setData('private_key', base64_encode($value));
    }

    public function getPrivateKey()
    {
        return base64_decode($this->getData('private_key'));
    }
}
