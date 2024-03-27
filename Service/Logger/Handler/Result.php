<?php
namespace Pointspay\Pointspay\Service\Logger\Handler;

use Pointspay\Pointspay\Service\Logger\Logger;

class Result extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/pointspay/{date}/result.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::RESULT;

    protected $level = Logger::RESULT;
}
