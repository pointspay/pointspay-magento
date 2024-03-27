<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pointspay\Pointspay\Service\FormatAmount;

class RefundAmountDataBuilder implements BuilderInterface
{
    /**
     * @var \Pointspay\Pointspay\Service\FormatAmount
     */
    private $formatAmount;

    /**
     * @param \Pointspay\Pointspay\Service\FormatAmount $formatAmount
     */
    public function __construct(
        FormatAmount $formatAmount
    ) {
        $this->formatAmount = $formatAmount;
    }


    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $creditMemo = $payment->getCreditMemo();
        $request['body']['amount'] = $this->formatAmount->formatAmount($creditMemo->getGrandTotal(), $creditMemo->getOrderCurrencyCode());
        return $request;
    }
}
