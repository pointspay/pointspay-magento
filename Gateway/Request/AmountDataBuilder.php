<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pointspay\Pointspay\Service\FormatAmount;

class AmountDataBuilder implements BuilderInterface
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
        $order = $paymentDataObject->getOrder();
        $request['body']['amount'] = $this->formatAmount->formatAmount($order->getGrandTotalAmount(), $order->getCurrencyCode());
        return $request;
    }
}
