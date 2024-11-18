<?php

namespace Pointspay\Pointspay\Service\Api;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;
use Pointspay\Pointspay\Service\Api\Checkout\BasePointspayPaymentMethodsService;

/**
 * Class FormValidationPointspayPaymentMethodsService
 *
 * @package Pointspay\Pointspay\Service\Api
 */
class FormValidationPointspayPaymentMethodsService
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Validator
     */
    protected $formKeyValidator;
    /**
     * @var BasePointspayPaymentMethodsService
     */
    private $paymentMethodsService;

    /**
     * AbstractInternalApiController constructor.
     * @param Http $request
     * @param Validator $formKeyValidator
     * @param BasePointspayPaymentMethodsService $paymentMethodsService
     */
    public function __construct(
        Http                               $request,
        Validator                          $formKeyValidator,
        BasePointspayPaymentMethodsService $paymentMethodsService
    )
    {
        $this->request = $request;
        $this->formKeyValidator = $formKeyValidator;
        $this->paymentMethodsService = $paymentMethodsService;
    }

    public function getAvailablePaymentMethods(string $cartId, string $formKey): array
    {
        $this->validateRequest($formKey);

        return $this->paymentMethodsService->getAvailablePaymentMethods($cartId);
    }

    /**
     * @param $formKey
     * @return bool
     * @throws \Exception
     */
    public function validateRequest($formKey): bool
    {
        $isAjax = $this->request->isAjax();
        // Post value has to be manually set since it will have no post data when this function is accessed
        $formKeyValid = $this->formKeyValidator->validate($this->request->setPostValue('form_key', $formKey));

        if (!$isAjax || !$formKeyValid) {
            throw new \Exception(
                'Invalid request',
                401
            );
        }

        return true;
    }
}
