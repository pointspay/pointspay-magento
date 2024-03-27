<?php

namespace Pointspay\Pointspay\Test\Model\Method\FakeBuilder;

use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundReason implements BuilderInterface
{

    public function build(array $buildSubject): array
    {
        $request['body']['refund_reason'] = 'some_reason';
        return $request;
    }
}
