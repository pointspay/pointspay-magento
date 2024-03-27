<?php

namespace Pointspay\Pointspay\Model\Framework\App\Config\Initital;

use Magento\Framework\App\Config\Initial\Converter;
use Magento\Framework\Stdlib\ArrayManager;
use Pointspay\Pointspay\Helper\Config;

class ConverterPlugin
{
    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;

    public function __construct(
        Config $config,
        ArrayManager $arrayManager
    ) {
        $this->config = $config;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param Converter $subject
     * @param array $result
     * @param \DOMDocument $source
     * @return array
     */
    public function afterConvert(Converter $subject, array $result, $source): array
    {
        $availablePayments = $this->config->getPaymentsReader()->getAvailablePointspayMethods() ?? [];
        $indexesToSearch = [\Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS];
        foreach ($availablePayments as $availablePayment) {
            $subPaymentCode = $availablePayment['pointspay_code'];
            if (strpos($subPaymentCode, '_required_settings') === false) {
                $subPaymentCode .= '_required_settings';
            }
            $indexesToSearch[] = $subPaymentCode;
        }
        $indexesToSearch = array_unique($indexesToSearch);
        $allPossibleGenericPaths = $this->arrayManager->findPaths(
            $indexesToSearch,
            $result
        ) ?? [];
        foreach ($allPossibleGenericPaths as $genericPath) {
            $valueToClone = $this->arrayManager->get(
                $genericPath,
                $result
            );
            foreach ($availablePayments as $availablePayment) {
                $subPaymentCode = $availablePayment['pointspay_code'];
                if (strpos($subPaymentCode, '_required_settings') === false) {
                    $subPaymentCode .= '_required_settings';
                }
                $newPath = str_replace(
                    \Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS,
                    $subPaymentCode,
                    $genericPath
                );
                $valueToCloneCandidate = $valueToClone;
                $valueToCloneCandidate['title'] = $availablePayment['name'];
                /**
                 * Please do not customize any settings here, checkout the following class instead:
                 * @see \Pointspay\Pointspay\Service\PaymentMethodsUpdater::execute
                 */
                if ($this->arrayManager->exists($newPath . '/model', $result)) {
                    continue;
                }
                $result = $this->arrayManager->set(
                    $newPath,
                    $result,
                    $valueToCloneCandidate
                );
            }
        }
        return $result;
    }
}
