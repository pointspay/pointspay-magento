<?php

namespace Pointspay\Pointspay\Test\Service\Logger\LoggerTest;

use Pointspay\Pointspay\Service\Logger\Logger;

/**
* @method bool hasResult($record)
* @method bool hasRequest($record)
 */
class HandlerTest extends \Monolog\Handler\TestHandler
{
    /**
     * @param  string  $method
     * @param  mixed[] $args
     * @return bool
     */
    public function __call($method, $args)
    {
        if (preg_match('/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/', $method, $matches) > 0) {
            $genericMethod = $matches[1] . ('Records' !== $matches[3] ? 'Record' : '') . $matches[3];
            $level = constant('Monolog\Logger::' . strtoupper($matches[2]));
            $callback = [$this, $genericMethod];
            if (is_callable($callback)) {
                $args[] = $level;

                return call_user_func_array($callback, $args);
            }
        }
        if (preg_match('/(.*)(Result|Request)(.*)/', $method, $matches) > 0) {
            $genericMethod = $matches[1] . ('Records' !== $matches[3] ? 'Record' : '') . $matches[3];
            $level = constant('Pointspay\Pointspay\Service\Logger\Logger::' . strtoupper($matches[2]));
            $callback = [$this, $genericMethod];
            if (is_callable($callback)) {
                $args[] = $level;

                return call_user_func_array($callback, $args);
            }
        }

        throw new \BadMethodCallException('Call to undefined method ' . get_class($this) . '::' . $method . '()');
    }
}
