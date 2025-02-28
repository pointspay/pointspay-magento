<?php

namespace Pointspay\Pointspay\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

class PaymentsReader
{
    private ScopeConfigInterface $scopeConfig;
    private SerializerInterface $serializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public function getAvailablePointspayMethods()
    {
        $availableMethodsList =  $this->scopeConfig->getValue('payment/pointspay_available_methods_list');
        $availableMethodsListDecoded = $this->serializer->unserialize($availableMethodsList ?? '[]');

        $availableMethods = [];
        foreach ($availableMethodsListDecoded as $method) {
            $availableMethods[$method['code']] = array_merge($method, ['pointspay_code' => $method['code']]);
        }

        return $availableMethods;
    }
}
