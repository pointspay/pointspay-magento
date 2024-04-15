<?php

namespace Pointspay\Pointspay\Model\Config\Structure\Data;

use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;
use Pointspay\Pointspay\Api\Data\StructureDataUpdaterInterface;

class PointspayRequiredSettings extends AbstractChain implements StructureDataUpdaterInterface
{
    public function execute($config)
    {
        $config = $this->restructurePayments($config);
        return $config;
    }

    protected function restructurePayments(array $config)
    {
        $sectionData = $config;
        $startPath = 'config/system/sections/payment/children';
        $startPath = $this->arrayManager->findPath('pointspay_group_all_in_one', $sectionData, $startPath);
        foreach ($this->paymentsReader->getAvailablePointspayMethods() as $key => $method) {
            $methodCode = $method['pointspay_code'];
            $methodTitle = $method['name'];
            $methodCodeForAccess = str_replace('_required_settings', '', $methodCode) . '_required_settings';
            if (!$this->arrayManager->exists($startPath . '/children/' . PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS . '/children/' . $methodCodeForAccess, $sectionData)) {
                $valueToClone = $this->arrayManager->get($startPath . '/children/' . PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS . '/children/' . PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $sectionData);
                $valueToClone['id'] = $methodCodeForAccess;
                //correcting config path for correct saving
                $valueToClone['path'] = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $methodCode . '_general_settings', $valueToClone['path']);
                // no need to clone label because it will replace "Basic Settings" label
                // $valueToClone['label'] = $methodTitle;
                $childrenItemsToIterate = isset($valueToClone['children']) ? $valueToClone['children'] : [];
                // replace pointspay with the actual method code(group + fieldset)
                array_walk_recursive(
                    $childrenItemsToIterate,
                    function (&$value, $key) use ($methodCode) {
                        if (is_string($value)) {
                            $newGroupName = str_replace('pointspay', $methodCode, PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS);
                            $value = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $newGroupName, $value);
                            $newFieldSetName = str_replace('pointspay', $methodCode, PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS);
                            $value = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $newFieldSetName, $value);
                            $newFieldSetName = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $methodCode . '_general_settings', $value);
                            $value = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $newFieldSetName, $value);
                        }
                    }
                );
                $childrenItemsToIterate = $this->modifyChild($childrenItemsToIterate, $methodCode);
                $valueToClone['children'] = $childrenItemsToIterate;
                unset($valueToClone['comment']);
                //Due \Magento\Paypal\Model\Config\StructurePlugin::$paypalConfigCountries
                // if you disable the Magento_Paypal Magento_PaypalGraphQl Magento_ReCaptchaPaypal Magento_PaypalCaptcha PayPal_Braintree PayPal_BraintreeGraphQl it won't need
                $payPalFix = [
                    'payment_us',
                    'payment_ca',
                    'payment_au',
                    'payment_gb',
                    'payment_jp',
                    'payment_fr',
                    'payment_it',
                    'payment_es',
                    'payment_hk',
                    'payment_nz',
                    'payment_de',
                    'payment_other'
                ];

                $payPalFixWithOriginal = array_merge($payPalFix, ['payment']);
                array_walk_recursive($payPalFixWithOriginal, function (&$value, $key) {
                    $value = $value . '/';
                });

                //prepare new fieldset and group names
                $newFieldSetName = str_replace('pointspay', $methodCode, PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS);
                $newGroupName = str_replace('pointspay', $methodCode, PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS);
                // fetch fieldset to copy from real payment method
                $fieldSetToCopy = $this->arrayManager->get($startPath . '/children/' . PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $sectionData);
                // process fieldset with current virtual payment method
                $fieldSetToCopy = $this->processFieldSet($fieldSetToCopy, $methodCode, $methodTitle, $payPalFixWithOriginal);
                // Firstly we need to add FieldSet due to \Magento\Config\Model\Config\Structure\Element\Iterator::setElements
                $sectionData = $this->insertInfoByPath($startPath, $newFieldSetName, $sectionData, $fieldSetToCopy);
                //then actually fields
                $sectionData = $this->arrayManager->set($startPath . '/children/' . $newFieldSetName . '/children/' . $methodCode . '_required_settings', $sectionData, $valueToClone);

                foreach ($payPalFix as $fixCountry) {
                    $modifiedStartPath = str_replace('payment', $fixCountry, $startPath ?: '');
                    if ($this->arrayManager->exists($modifiedStartPath . '/children/' . $newFieldSetName . '/children/' . $newGroupName, $sectionData)) {
                        continue;
                    }
                    array_walk_recursive($valueToClone, function (&$value, $key) use ($methodCode, $fixCountry, $payPalFixWithOriginal) {
                        if ($key == 'path' && is_string($value) && strpos($value, $fixCountry) === false) {
                            $value = str_replace($payPalFixWithOriginal, $fixCountry . '/', $value ?: '');
                        }
                    });
                    $sectionData = $this->repeatForEachPayPalCountry($modifiedStartPath, $newFieldSetName, $sectionData, $fieldSetToCopy, $valueToClone, $methodCode . '_required_settings', $payPalFixWithOriginal, $fixCountry);
                }
            }
        }
        $config = $sectionData;
        return $config;
    }

    /**
     * Addtitional logic to modify children
     *
     * @param $childrenItemsToIterate
     * @param $methodCode
     * @return array
     */
    protected function modifyChild($childrenItemsToIterate, $methodCode)
    {
        foreach ($childrenItemsToIterate as $keyChild => $valueChild) {
            if (is_string($valueChild)) {
                $valueChild = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $methodCode, $valueChild);
                $newFieldSetName = str_replace('pointspay', $methodCode, PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS);
                $valueChild = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $newFieldSetName, $valueChild);
                $newFieldSetName = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $methodCode . '_general_settings', $valueChild);
                $valueChild = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $newFieldSetName, $valueChild);
                $childrenItemsToIterate[$keyChild] = $valueChild;
            } else {
                $this->modifyChild($valueChild, $methodCode);
            }
        }
        return $childrenItemsToIterate;
    }

    protected function processFieldSet($fieldSetToCopy, $methodCode, $methodTitle)
    {
        $originalFieldSetToCopy = $fieldSetToCopy;
        array_walk_recursive(
            $fieldSetToCopy,
            function (&$value) use ($methodCode) {
                if (is_string($value)) {
                    $originalValue = $value;
                    $newGroupName = str_replace('pointspay', $methodCode, PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS);
                    $value = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $newGroupName, $value);
                    $newFieldSetName = str_replace('pointspay', $methodCode, PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS);
                    $value = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $newFieldSetName, $value);
                    $newFieldSetName = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $methodCode . '_general_settings', $value);
                    $value = str_replace(PointspayGeneralPaymentInterface::POINTSPAY_GENERAL_SETTINGS, $newFieldSetName, $value);
                }
            }
        );
        $fieldSetToCopy = $this->arrayManager->remove('children', $fieldSetToCopy);
        $fieldSetToCopy = $this->arrayManager->replace('label', $fieldSetToCopy, $methodTitle);
        return $fieldSetToCopy;
    }

}
