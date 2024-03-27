<?php

namespace Pointspay\Pointspay\Test\Model\Method\FakeBuilder;

use Magento\Payment\Gateway\Request\BuilderInterface;

class PaymentId implements BuilderInterface
{

    public function build(array $buildSubject): array
    {
        $request['body']['payment_id'] = 'ab7b63b1b9e34163989eea27bf575486';
        return $request;
    }
}
