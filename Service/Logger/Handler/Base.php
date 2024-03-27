<?php

namespace Pointspay\Pointspay\Service\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base as MagentoBase;

class Base extends MagentoBase
{

    public function __construct(
        DriverInterface $filesystem,
        ?string $filePath = null,
        ?string $fileName = null
    ) {
        $currentDate = new \DateTime();
        $currentDateString =  $currentDate->format('d-m-Y');
        $genericPath = 'var/log/pointspay/generic/info.log';
        $newFileName = str_replace('{date}', $currentDateString, $this->fileName?:$genericPath);
        $this->fileName = $newFileName;
        $fileName = $newFileName;
        parent::__construct($filesystem, $filePath, $fileName);
    }

    /**
     * overwrite core it needs to be the exact level otherwise use different handler
     *
     * {@inheritdoc}
     */
    public function isHandling(array $record): bool
    {
        return $record['level'] == $this->level;
    }
}
