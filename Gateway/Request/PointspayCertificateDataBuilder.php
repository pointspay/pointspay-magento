<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Service\CertificateHandler;

class PointspayCertificateDataBuilder implements BuilderInterface
{
    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $config;

    /**
     * @param \Pointspay\Pointspay\Helper\Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
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
        $paymentCode = $paymentDataObject->getPayment()->getAdditionalInformation('pointspay_flavor');
        $pointspayCertificate = $this->config->getPointspayCertificate($paymentCode, $order->getStoreId());
        $request['clientConfig']['key_info']['certificate'] = $pointspayCertificate;
        return $request;
    }
}
