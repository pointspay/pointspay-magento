<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Service\CertificateHandler;

class PrivateKeyDataBuilder implements BuilderInterface
{
    /**
     * @var \Pointspay\Pointspay\Service\CertificateHandler
     */
    private $certificateHandler;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Pointspay\Pointspay\Service\CertificateHandler $certificateHandler
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Pointspay\Pointspay\Helper\Config $configHelper
     */
    public function __construct(
        CertificateHandler $certificateHandler,
        StoreManagerInterface $storeManager,
        Config $configHelper
    ) {
        $this->certificateHandler = $certificateHandler;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
    }


    /**
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $order = $paymentDataObject->getOrder();
        $paymentCode = $paymentDataObject->getPayment()->getAdditionalInformation('pointspay_flavor');
        $storeId = $order->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $keychain = $this->certificateHandler->get($paymentCode, $websiteId);
        $request['clientConfig']['key_info']['private_key'] = $keychain->getPrivateKey();
        return $request;
    }
}
