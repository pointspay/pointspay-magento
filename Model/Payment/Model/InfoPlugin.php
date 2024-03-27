<?php

namespace Pointspay\Pointspay\Model\Payment\Model;

use Magento\Payment\Model\Info;
use Magento\Payment\Model\MethodInterface;
use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;
use Pointspay\Pointspay\Helper\Config;

class InfoPlugin
{
    public static $pointspayMethodsCache = [];

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    public function __construct(
        Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }
    /**
    * @param Info $subject
    * @param MethodInterface $result
    * @return MethodInterface
     */
    public function afterGetMethodInstance(Info $subject, MethodInterface $result): MethodInterface
    {
        if ($subject->getMethod() !== PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS && $result->getCode() === PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS) {
            $result = $this->processMethodInstance($result, $subject);
        }
        return $result;
    }

    public function processMethodInstance($paymentMethod, $result)
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
        $paymentCodeWithoutSuffix = str_replace('_required_settings', '', $result->getMethod());
        if (!in_array($paymentCodeWithoutSuffix, array_keys($listOfEnabledPaymentMethodsArr))) {
            return $result;
        }
        $methodInstance = $paymentMethod;
        $reflection = new \ReflectionClass($methodInstance);
        $parentClassReflection = $reflection->getParentClass();
        $subParentClassReflection = null;
        if ($parentClassReflection) {
            $subParentClassReflection = $parentClassReflection->getParentClass();
        }
        if ($parentClassReflection->hasProperty("code")) {
            $property = $parentClassReflection->getProperty('code');
            if ($property->isInitialized($methodInstance)) {
                $property->setAccessible(true);
                $paymentMethodCode = $result->getMethod();
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
            $paymentMethodCode = $result->getMethod();
            if (strpos($paymentMethodCode, '_required_settings') === false) {
                $paymentMethodCode .= '_required_settings';
            }
            $property->setValue($methodInstance, $paymentMethodCode);
        }
        return $methodInstance;
    }

}
