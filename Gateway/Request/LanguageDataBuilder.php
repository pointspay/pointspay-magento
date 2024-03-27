<?php

namespace Pointspay\Pointspay\Gateway\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class LanguageDataBuilder implements BuilderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
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
        $storeId = $order->getStoreId();
        $languageCode = $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId);
        $langISO = explode('_', $languageCode ?: 'en_US');
        $request['body']['language'] = reset($langISO);
        return $request;
    }
}
