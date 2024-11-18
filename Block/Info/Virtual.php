<?php

namespace Pointspay\Pointspay\Block\Info;

use Pointspay\Pointspay\Api\PaymentMethodsInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;

class Virtual extends \Magento\Payment\Block\Info
{
    protected $_template = 'Pointspay_Pointspay::info/virtual.phtml';

    private $paymentMethods;

    public function __construct(
        Template\Context $context,
        PaymentMethodsInterface $payment,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentMethods = $payment;
    }

    /**
     * Returns the display title for a given payment flavor code.
     *
     * @param string $flavorCode The payment flavor code to look up.
     * @return string The displayable title for the payment method.
     */
    public function getPaymentTitleByFlavor($flavorCode)
    {
        return $this->paymentMethods->getPaymentTitleByCode($flavorCode);
    }
}
