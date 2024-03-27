<?php

namespace Pointspay\Pointspay\Service\Logger\Handler;

use Pointspay\Pointspay\Service\Logger\Logger;


class Debug extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/pointspay/{date}/debug.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    protected $level = Logger::DEBUG;
}
