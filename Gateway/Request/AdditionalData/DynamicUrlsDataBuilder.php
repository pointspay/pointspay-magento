<?php

namespace Pointspay\Pointspay\Gateway\Request\AdditionalData;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Api\Data\ApiInterface;

class DynamicUrlsDataBuilder implements BuilderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
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
        $baseUrl = $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE, $storeId);
        $request['body']['additional_data']['dynamic_urls']['success'] = $baseUrl . ApiInterface::POINTSPAY_SUCCESS_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['cancel'] = $baseUrl . ApiInterface::POINTSPAY_CANCEL_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['failure'] = $baseUrl . ApiInterface::POINTSPAY_FAIL_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['ipn'] = $baseUrl . ApiInterface::REST_IPN_SUFFIX;
        return $request;
    }
}
