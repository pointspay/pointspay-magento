<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class PaymentCodeDataBuilder implements BuilderInterface
{
    /**
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $paymentCode = $paymentDataObject->getPayment()->getAdditionalInformation('pointspay_flavor') . '_required_settings';
        $request['clientConfig']['payment_code'] = $paymentCode;
        return $request;
    }
}
