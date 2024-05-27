<?php

namespace Pointspay\Pointspay\Service\Checkout;

use Exception;
use Magento\Authorization\Model\UserContextInterface;
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
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @param \Pointspay\Pointspay\Service\Checkout\MerchantAppHref $merchantAppHref
     */
    public function __construct(
        MerchantAppHref $merchantAppHref,
        Validator $formKeyValidator,
        \Magento\Framework\App\Request\Http $request,
        UserContextInterface $userContext
    ) {
        $this->merchantAppHref = $merchantAppHref;
        $this->formKeyValidator = $formKeyValidator;
        $this->request = $request;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritDoc}
     */
    public function getMerchantAppHref($quoteId)
    {
        $this->validateHrefRequest();
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            $customerId = $this->userContext->getUserId();
            return $this->merchantAppHref->get($quoteId, $customerId);
        }
        //we have to throw an exception if the user is not a customerSession but other request to access this API
        throw new Exception('You are not authorized to perform this action.');
    }
}
