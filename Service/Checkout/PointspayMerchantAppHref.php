<?php

namespace Pointspay\Pointspay\Service\Checkout;

use Magento\Framework\Data\Form\FormKey\Validator;
use Pointspay\Pointspay\Api\PointspayMerchantAppHrefInterface;

class PointspayMerchantAppHref implements PointspayMerchantAppHrefInterface
{
    use  MerchantAppHrefValidatorTrait;

    /**
     * @var \Pointspay\Pointspay\Service\Checkout\MerchantAppHref
     */
    private $merchantAppHref;

    /**
     * @param \Pointspay\Pointspay\Service\Checkout\MerchantAppHref $merchantAppHref
     */
    public function __construct(
        MerchantAppHref $merchantAppHref,
        Validator $formKeyValidator,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->merchantAppHref = $merchantAppHref;
        $this->formKeyValidator = $formKeyValidator;
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getMerchantAppHref($quoteId)
    {
        $this->validateHrefRequest();
        return $this->merchantAppHref->get($quoteId);
    }
}
