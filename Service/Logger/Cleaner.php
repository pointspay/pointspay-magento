<?php

namespace Pointspay\Pointspay\Service\Logger;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime;

class Cleaner
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystem;


    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystem
     */
    public function __construct(
        File $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $thirtyDaysAgo = new \DateTime();
        $thirtyDaysAgoTimeStamp = $thirtyDaysAgo->modify("-30 days")->setTime(0,0)->getTimestamp();
        $docs = $this->filesystem->readDirectory(BP . '/var/log/pointspay/');
        foreach ($docs as $doc) {
            if (!$this->filesystem->isDirectory($doc)) {
                continue;
            }
            $onlyDate = explode('/', $doc);
            $givenDateTimeStamp = strtotime(end($onlyDate));
            if ($givenDateTimeStamp < $thirtyDaysAgoTimeStamp) {
                $this->filesystem->deleteDirectory($doc);
            }
        }
    }
}
