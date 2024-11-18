<?php

namespace Pointspay\Pointspay\Model\File;

use Exception;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Validation\ValidationException;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;

class Uploader extends \Magento\MediaStorage\Model\File\Uploader
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $scopeId;

    public function __construct(
        $fileId,
        Database $coreFileStorageDb,
        Storage $coreFileStorage,
        NotProtectedExtension $validator,
        WriterInterface $configWriter,
        Filesystem $filesystem = null
    ) {
        parent::__construct($fileId, $coreFileStorageDb, $coreFileStorage, $validator);
        $this->configWriter = $configWriter;
    }

    /**
     * Used to save uploaded file content into DB.
     *
     * @return string|null
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveCertificate()
    {
        try {
            $this->_validateFile();
        } catch (ValidationException $e) {
            return null;
        }
        $certificateContent = file_get_contents($this->_file['tmp_name']);
        $this->configWriter->save($this->code, $certificateContent, $this->scope, $this->scopeId);
        return $certificateContent;
    }

    /**
     * @param $scope
     * @return void
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param $scopeId
     * @return void
     */
    public function setScopeId($scopeId)
    {
        $this->scopeId = $scopeId;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }


}
