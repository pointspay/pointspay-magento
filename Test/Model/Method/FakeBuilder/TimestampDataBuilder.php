<?php

namespace Pointspay\Pointspay\Test\Model\Method\FakeBuilder;

class TimestampDataBuilder extends \Pointspay\Pointspay\Gateway\Request\TimestampDataBuilder
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $request['clientConfig']['oauth']['timestamp'] = (int)floor(microtime(true) * 1000);
        return $request;
    }
}
