<?php

namespace Pointspay\Pointspay\Model\Payment\Model\Method;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Payment\Model\MethodInterface;
use Pointspay\Pointspay\Helper\Config;
use ReflectionClass;

class InstanceFactoryPlugin
{
    static $pointspayMethodsCache = [];

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Pointspay\Pointspay\Helper\Config $configHelper
     */
    public function __construct(
        Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * @param InstanceFactory $subject
     * @param MethodInterface $result
     * @param PaymentMethodInterface $paymentMethod
     * @return MethodInterface
     * @throws \ReflectionException
     */
    public function afterCreate(InstanceFactory $subject, MethodInterface $result, PaymentMethodInterface $paymentMethod): MethodInterface
    {
        $listOfEnabledPaymentMethodsArr = [];
        if (!empty(static::$pointspayMethodsCache)) {
            $listOfEnabledPaymentMethodsArr = static::$pointspayMethodsCache;
        } else {
            $listOfEnabledPaymentMethods = $this->configHelper->getEnabledPaymentMethodsDetails();
            foreach ($listOfEnabledPaymentMethods as $enabledPaymentMethod) {
                $listOfEnabledPaymentMethodsArr[$enabledPaymentMethod['pointspay_code']] = $enabledPaymentMethod['name'] ?: $enabledPaymentMethod['title'];
            }
            static::$pointspayMethodsCache = $listOfEnabledPaymentMethodsArr;
        }
        $paymentCodeWithoutSuffix = str_replace('_required_settings', '', $paymentMethod->getCode());
        if (!in_array($paymentCodeWithoutSuffix, array_keys($listOfEnabledPaymentMethodsArr))) {
            return $result;
        }
        $methodInstance = $result;
        $reflection = new ReflectionClass($methodInstance);
        $parentClassReflection = $reflection->getParentClass();
        $subParentClassReflection = null;
        if ($parentClassReflection) {
            $subParentClassReflection = $parentClassReflection->getParentClass();
        }
        if ($parentClassReflection->hasProperty("code")) {
            $property = $parentClassReflection->getProperty('code');
            $property->setAccessible(true);
            if ($property->isInitialized($methodInstance)) {
                $paymentMethodCode = $paymentMethod->getCode();
                if (strpos($paymentMethodCode, '_required_settings') === false) {
                    $paymentMethodCode .= '_required_settings';
                }
                $property->setValue($methodInstance, $paymentMethodCode);
            }
        } elseif (!$parentClassReflection->hasProperty("code") && $subParentClassReflection) {
            $property = $subParentClassReflection->getProperty('code');
            if (!$property) {
                return $result;
            }
            $property->setAccessible(true);
            $paymentMethodCode = $paymentMethod->getCode();
            if (strpos($paymentMethodCode, '_required_settings') === false) {
                $paymentMethodCode .= '_required_settings';
            }
            $property->setValue($methodInstance, $paymentMethodCode);
        }
        return $result;
    }
}
