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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $order = $paymentDataObject->getOrder();
        $storeId = $order->getStoreId();
        //experimental. bc each merchant in own way can route the urls
        $addStoreCodeSettings = $this->scopeConfig->getValue('web/url/use_store');
        $baseUrl = $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE, $storeId);
        if ($addStoreCodeSettings) {
            $baseUrl .= $this->storeManager->getStore($storeId)->getCode() . '/';
        }
        $request['body']['additional_data']['dynamic_urls']['success'] = $baseUrl . ApiInterface::POINTSPAY_SUCCESS_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['cancel'] = $baseUrl . ApiInterface::POINTSPAY_CANCEL_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['failure'] = $baseUrl . ApiInterface::POINTSPAY_FAIL_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['ipn'] = $baseUrl . ApiInterface::REST_IPN_SUFFIX;
        return $request;
    }
}
