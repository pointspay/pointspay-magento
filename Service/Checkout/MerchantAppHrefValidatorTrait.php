<?php

namespace Pointspay\Pointspay\Service\Checkout;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Webapi\Exception;

trait MerchantAppHrefValidatorTrait
{


    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Framework\App\HttpRequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        Validator $formKeyValidator,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->request = $request;
    }

    /**
     * @param $formKey
     * @return bool
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function validateHrefRequest()
    {
        $isAjax = $this->request->isAjax();
        // Post value has to be manually set since it will have no post data when this function is accessed
        $formKeyValid = $this->formKeyValidator->validate($this->request);

        if (!$isAjax || !$formKeyValid) {
            throw new Exception(
                __('Invalid request'),
                401
            );
        }

        return true;
    }
}
