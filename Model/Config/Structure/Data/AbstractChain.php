<?php

namespace Pointspay\Pointspay\Model\Config\Structure\Data;

use Magento\Framework\Stdlib\ArrayManager;
use Pointspay\Pointspay\Service\PaymentsReader;
use Psr\Log\LoggerInterface;

class AbstractChain
{
    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \Pointspay\Pointspay\Service\PaymentsReader
     */
    protected $paymentsReader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        ArrayManager $arrayManager,
        PaymentsReader $paymentsReader,
        LoggerInterface $logger
    ) {
        $this->arrayManager = $arrayManager;
        $this->paymentsReader = $paymentsReader;
        $this->logger = $logger;
    }
    /**
     * @param string|null $startPath
     * @param $newFieldSetName
     * @param array $sectionData
     * @param array $fieldSetToCopy
     * @return array
     */
    protected function insertInfoByPath($startPath, $newFieldSetName,$sectionData, $fieldSetToCopy): array
    {
        if (!$this->arrayManager->exists($startPath . '/children/' . $newFieldSetName, $sectionData)) {
            $sectionData = $this->arrayManager->set($startPath . '/children/' . $newFieldSetName, $sectionData, $fieldSetToCopy);
        } else {
            $childNodes = $this->arrayManager->get($startPath . '/children/' . $newFieldSetName . '/children', $sectionData);
            $sectionData = $this->arrayManager->merge($startPath . '/children/' . $newFieldSetName, $sectionData, $fieldSetToCopy);
            $sectionData = $this->arrayManager->merge($startPath . '/children/' . $newFieldSetName . '/children', $sectionData, $childNodes);
        }
        return $sectionData;
    }
    /**
     * @param $modifiedStartPath
     * @param $newFieldSetName
     * @param array $sectionData
     * @param array $fieldSetToCopy
     * @param $methodCode
     * @param $valueToClone
     * @param $settingScope
     * @return array
     */
    protected function repeatForEachPayPalCountry($modifiedStartPath, $newFieldSetName, array $sectionData, array $fieldSetToCopy, $valueToClone, $settingScope, $payPalFixWithOriginal, $fixCountry): array
    {
        array_walk_recursive($fieldSetToCopy, function (&$value, $key) use ($fixCountry, $payPalFixWithOriginal) {
            if ($key == 'path' && is_string($value) && strpos($value, $fixCountry) === false) {
                $value = str_replace($payPalFixWithOriginal, $fixCountry . '/', $value ?: '');
            }
        });
        if (!$this->arrayManager->exists($modifiedStartPath . '/children/' . $newFieldSetName, $sectionData)) {
            $sectionData = $this->arrayManager->set($modifiedStartPath . '/children/' . $newFieldSetName, $sectionData, $fieldSetToCopy);
            $sectionData = $this->arrayManager->set($modifiedStartPath . '/children/' . $newFieldSetName . '/children/' . $settingScope, $sectionData, $valueToClone);
        } else {
            $childNodes = $this->arrayManager->get($modifiedStartPath . '/children/' . $newFieldSetName . '/children', $sectionData);
            $sectionData = $this->arrayManager->merge($modifiedStartPath . '/children/' . $newFieldSetName, $sectionData, $fieldSetToCopy);
            $sectionData = $this->arrayManager->set($modifiedStartPath . '/children/' . $newFieldSetName . '/children/' . $settingScope, $sectionData, $valueToClone);
            $sectionData = $this->arrayManager->merge($modifiedStartPath . '/children/' . $newFieldSetName . '/children', $sectionData, $childNodes);
        }
        return $sectionData;
    }
}
