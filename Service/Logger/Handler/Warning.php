<?php
namespace Pointspay\Pointspay\Service\Logger\Handler;

use Pointspay\Pointspay\Service\Logger\Logger;

class Warning extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/pointspay/{date}/warning.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::WARNING;

    protected $level = Logger::WARNING;
}
