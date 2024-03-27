<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundReasonDataBuilder implements BuilderInterface
{

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $creditMemo = $payment->getCreditMemo();
        $request['body']['refund_reason'] = !empty($creditMemo->getCustomerNote()) ? $creditMemo->getCustomerNote() : 'refund';
        return $request;
    }
}
