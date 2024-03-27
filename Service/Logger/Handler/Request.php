<?php
namespace Pointspay\Pointspay\Service\Logger\Handler;

use Pointspay\Pointspay\Service\Logger\Logger;

class Request extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/pointspay/{date}/request.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::REQUEST;

    protected $level = Logger::REQUEST;
}
