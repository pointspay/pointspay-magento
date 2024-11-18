<?php

namespace Pointspay\Pointspay\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

class File extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @var \Pointspay\Pointspay\Model\File\UploaderFactory
     */
    private $uploaderCertificateFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Pointspay\Pointspay\Model\File\UploaderFactory $uploaderCertificateFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        Filesystem $filesystem,
        \Pointspay\Pointspay\Model\File\UploaderFactory $uploaderCertificateFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
        $this->uploaderCertificateFactory = $uploaderCertificateFactory;
    }

    /**
     * Save uploaded file before saving config value
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $file = $this->getFileData();

        if (!empty($file)) {
            try {
                /** @var \Pointspay\Pointspay\Model\File\Uploader $uploader */
                $uploader = $this->uploaderCertificateFactory->create(['fileId' => $file]);
                $uploader->setAllowedExtensions($this->_getAllowedExtensions());
                $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                $uploader->setScope($this->getScope());
                $uploader->setScopeId($this->getScopeId());
                $uploader->setCode($this->getPath());
                $result = $uploader->saveCertificate();
                if ($result) {
                    $this->setValue($result);
                }
            } catch (\Exception $e) {
                throw new LocalizedException(__('%1', $e->getMessage()));
            }
        } else {
            //delete functionality
            if (is_array($value) && !empty($value['delete'])) {
                $this->setValue('');
            } elseif (is_array($value) && !empty($value['value'])) {
                $this->setValue($value['value']);
            } else {
                $this->unsValue();
            }
        }

        return $this;
    }
    protected function _getAllowedExtensions()
    {
        return ['cer'];
    }
}
