<?php

namespace Pointspay\Pointspay\Test\Model\Method\FakeBuilder;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;

class PaymentCodeDataBuilder extends \Pointspay\Pointspay\Gateway\Request\PaymentCodeDataBuilder
{
    public function build(array $buildSubject): array
    {

        $request['clientConfig']['payment_code'] = 'pointspay_required_settings';
        return $request;
    }
}
