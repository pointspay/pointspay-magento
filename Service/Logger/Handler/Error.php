<?php
namespace Pointspay\Pointspay\Service\Logger\Handler;

use Pointspay\Pointspay\Service\Logger\Logger;

class Error extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/pointspay/{date}/error.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * @var
     */
    protected $level = Logger::ERROR;
}
