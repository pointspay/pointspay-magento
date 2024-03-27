<?php

namespace Pointspay\Pointspay\Model\Config\Payment;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\Data\PaymentMethodInterfaceFactory;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\PaymentMethod;
use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;
use Pointspay\Pointspay\Helper\Config;

class PaymentMethodListPlugin
{
    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Payment\Api\Data\PaymentMethodInterfaceFactory
     */
    private $methodFactory;

    /**
     * @param \Pointspay\Pointspay\Helper\Config $configHelper
     * @param \Magento\Payment\Api\Data\PaymentMethodInterfaceFactory $methodFactory
     */
    public function __construct(
        Config $configHelper,
        PaymentMethodInterfaceFactory $methodFactory
    ) {
        $this->configHelper = $configHelper;
        $this->methodFactory = $methodFactory;
    }

    /**
     * @param PaymentMethodListInterface $subject
     * @param PaymentMethodInterface[] $result
     * @param int $storeId
     * @return PaymentMethodInterface[]
     */
    public function afterGetList(PaymentMethodListInterface $subject, array $result, $storeId): array
    {
        $pointspayGeneralPaymentAlreadyProcessed = false;
        $pointsPayAvailableMethods = $this->configHelper->getEnabledPaymentMethodsDetails();
        $pointsPayAvailableMethodsResorted = [];
        foreach ($pointsPayAvailableMethods as $availableMethod) {
            $pointsPayAvailableMethodsResorted[$availableMethod['code']] = $availableMethod;
        }
        $methodList = array_map(
            function (PaymentMethod $methodInstance) use (&$pointspayGeneralPaymentAlreadyProcessed, &$pointsPayAvailableMethodsResorted) {
                if (!$pointspayGeneralPaymentAlreadyProcessed) {
                    if ($methodInstance->getCode() === PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS) {
                        $pointspayGeneralPaymentAlreadyProcessed = true;
                        unset($pointsPayAvailableMethodsResorted[PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS]);
                        return $methodInstance;
                    }
                }
                foreach ($pointsPayAvailableMethodsResorted as $methodKey => &$availableMethod) {
                    if ($methodInstance->getCode() === PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS) {
                        $newMethod = $this->methodFactory->create(
                            [
                                'code' => $availableMethod['code'],
                                'title' => $availableMethod['title'],
                                'storeId' => $methodInstance->getStoreId(),
                                'isActive' => $availableMethod['isActive'] ?: $methodInstance->getIsActive(),
                            ]
                        );
                        unset($pointsPayAvailableMethodsResorted[$methodKey]);
                        return $newMethod;
                    }
                }
                return $methodInstance;
            },
            $result
        ) ?? null;
        return $methodList ?? $result;
    }
}
