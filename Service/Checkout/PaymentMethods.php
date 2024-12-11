<?php

namespace Pointspay\Pointspay\Service\Checkout;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Api\PaymentMethodsInterface;

/**
 * Class PaymentMethods
 *
 * Service class that provides methods to retrieve and manage available payment methods
 * based on Pointspay configurations.
 */
class PaymentMethods implements PaymentMethodsInterface
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * PaymentMethods constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface  $serializer
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentTitleByCode($flavorCode)
    {
        $jsonData = $this->scopeConfig->getValue('payment/pointspay_available_methods_list');
        $availableMethods = $this->serializer->unserialize($jsonData ?? '[]');

        foreach ($availableMethods as $method) {
            if ($method['code'] === $flavorCode) {
                return $method['name'];
            }
        }

        return $flavorCode;
    }
}
