<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Helper\Config;

class ConsumerKeyDataBuilder implements BuilderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Pointspay\Pointspay\Helper\Config $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $order = $paymentDataObject->getOrder();
        $storeId = $order->getStoreId();
        $paymentCode = $paymentDataObject->getPayment()->getAdditionalInformation('pointspay_flavor');
        $consumerKey = $this->config->getConsumerKey($paymentCode, $storeId);
        $request['clientConfig']['oauth']['consumer_key'] = $consumerKey;
        return $request;
    }
}
