<?php

namespace Pointspay\Pointspay\Test\Model\Method\FakeBuilder;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;

class AmountDataBuilder extends \Pointspay\Pointspay\Gateway\Request\AmountDataBuilder
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $request['body']['amount'] = 1222;
        return $request;
    }
}
