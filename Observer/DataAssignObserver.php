<?php

namespace Pointspay\Pointspay\Observer;

use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class DataAssignObserver
 *
 * @package Vendor\Module\Observer
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const POINTSPAY_FLAVOR_KEY = 'pointspay_flavor';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::POINTSPAY_FLAVOR_KEY,
    ];

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
