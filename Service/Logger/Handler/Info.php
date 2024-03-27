<?php
namespace Pointspay\Pointspay\Service\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Pointspay\Pointspay\Service\Logger\Logger;

class Info extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/pointspay/{date}/info.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    protected $level = Logger::INFO;
}
