<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pointspay\Pointspay\Service\Uuid;

class NonceDataBuilder implements BuilderInterface
{
    /**
     * @var \Pointspay\Pointspay\Service\Uuid
     */
    private $uuid;

    public function __construct(
        Uuid $uuid
    ) {
        $this->uuid = $uuid;
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
        $request['clientConfig']['oauth']['nonce'] = $this->uuid->generateV4();
        return $request;
    }
}
