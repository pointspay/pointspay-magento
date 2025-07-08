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
    protected $loggerType = Logger::INFO;

    /**
     * @var int|null
     */
    protected $infoType = Logger::REQUEST;
}
