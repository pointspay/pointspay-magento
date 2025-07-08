<?php

namespace Pointspay\Pointspay\Service\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base as MagentoBase;
use Monolog\LogRecord;
use Pointspay\Pointspay\Service\Logger\Logger;

class Base extends MagentoBase
{
    /**
     * @var int|null
     */
    protected $infoType = null;

    public function __construct(
        DriverInterface $filesystem,
        ?string $filePath = null
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
     * @param array|LogRecord $record The log record to handle.
     *
     * {@inheritdoc}
     */
    public function isHandling($record): bool
    {
        // New versions of Magento (2.4.8+)
        if ($record instanceof LogRecord) {
            if ($record->level->value !== Logger::INFO) {
                return $record->level === $this->level;
            }

            if (!$this->infoType || !$record->context || !isset($record->context['infoType'])) {
                return false;
            }

            return $record->level === $this->level && $record->context['infoType'] === $this->infoType;
        }

        // Older Magento versions (< 2.4.8)
        if (is_array($record) && isset($record['level'])) {
            if (!in_array($record['level'], [Logger::INFO, Logger::REQUEST, Logger::RESULT], true)) {
                return $record['level'] === $this->level;
            }

            return $this->infoType && $record['level'] === $this->infoType;
        }

        return false;
    }
}
