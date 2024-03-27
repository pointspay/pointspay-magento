<?php

namespace Pointspay\Pointspay\Api\Data;

interface PointspayGeneralPaymentInterface
{
    /**
     * the name of group of settings for the base(non-virtual) settings of the base payment method
     * in the virtual payment method settings it will be changed to "<payment_code>_general_settings" way
     */
    const POINTSPAY_GENERAL_SETTINGS = 'pointspay_general_settings';

    /**
     * this is the base name for non-virtual payment methods, basically it the code of the base payment method
     * which will be changed to the virtual payment method code in "<payment_code>_required_settings" way
     * @see \Pointspay\Pointspay\Model\Config\Structure\Data
     * @see \Pointspay\Pointspay\Model\Framework\App\Config\Initital\ConverterPlugin
     * @see \Pointspay\Pointspay\Model\Config\Payment\PaymentMethodListPlugin
     * @see \Pointspay\Pointspay\Block\Payment\Block\Form\ContainerPlugin
     */
    const POINTSPAY_REQUIRED_SETTINGS = 'pointspay_required_settings';

    /**
     * Main URL for LIVE environment
     * (hardcoded by the payment provider, so it is not configurable in the admin panel)
     */
    const POINTSPAY_LIVE_URL = 'https://secure.pointspay.com/';
    /**
     * Main URL for SANDBOX environment
     * (hardcoded by the payment provider, so it is not configurable in the admin panel)
     */
    const POINTSPAY_SANDBOX_URL = 'https://uat-secure.pointspay.com/';

    const POINTSPAY_ACCESS_SETTINGS = 'pointspay_access_settings';

}
