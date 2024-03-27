<?php

namespace Pointspay\Pointspay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Pointspay\Pointspay\Service\PaymentsReader;

class EnabledMethods implements OptionSourceInterface
{

    /**
     * @var \Pointspay\Pointspay\Service\PaymentsReader
     */
    private $paymentsReader;

    public function __construct(PaymentsReader $paymentsReader)
    {
        $this->paymentsReader = $paymentsReader;
    }


    /**
     * @return array
     */
    public function toOptionArray()
    {
        $availableMethods = $this->paymentsReader->getAvailablePointspayMethods();
        $options = [];
        foreach ($availableMethods as $method) {
            $options[] = [
                'value' => $method['pointspay_code'],
                'label' => $method['name']
            ];
        }
        return $options;
    }
}
