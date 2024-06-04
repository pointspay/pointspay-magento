<?php

namespace Pointspay\Pointspay\Service\PaymentMethodsUpdater;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

class FileResolver implements FileResolverInterface
{
    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    private $iteratorFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $filesystemIo;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    public function __construct(
        FileIteratorFactory $iteratorFactory,
        Filesystem $filesystem,
        File $filesystemIo,
        Reader $moduleReader
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->filesystem = $filesystem;
        $this->filesystemIo = $filesystemIo;
        $this->moduleReader = $moduleReader;
    }

    /**
     * @inheritDoc
     */
    public function get($filename, $scope)
    {
        $mediaAbsPath = sprintf('/%s%s', trim($this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('pointspay'), '/'), '/');
        $pubFolderExist = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->isExist($mediaAbsPath);
        if ($pubFolderExist === false) {
            $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->create('pointspay');
        }
        $filename = sprintf('%s%s', $mediaAbsPath, $filename);
        if ($this->filesystemIo->fileExists($filename) === false) {
            $etcDir = $this->moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Pointspay_Pointspay');
            $this->filesystemIo->cp(sprintf('%s/%s', $etcDir, 'pointspay_methods.xml'), $filename);
        }
        $iterator = $this->iteratorFactory->create([$filename]);
        return $iterator;
    }
}
