<?php
namespace Pointspay\Pointspay\Service\Logger\Handler;

use Pointspay\Pointspay\Service\Logger\Logger;

class Critical extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/pointspay/{date}/critical.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::CRITICAL;

    /**
     * @var
     */
    protected $level = Logger::CRITICAL;
}
