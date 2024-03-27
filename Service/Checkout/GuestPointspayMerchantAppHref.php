<?php

namespace Pointspay\Pointspay\Service\Checkout;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask;
use Pointspay\Pointspay\Api\GuestPointspayMerchantAppHrefInterface;

class GuestPointspayMerchantAppHref implements GuestPointspayMerchantAppHrefInterface
{
    use MerchantAppHrefValidatorTrait;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Pointspay\Pointspay\Service\Checkout\MerchantAppHref
     */
    private $merchantAppHref;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask
     */
    private $quoteIdMaskResourceModel;

    /**
     * GuestAdyenPaymentMethodManagement constructor.
     *
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Pointspay\Pointspay\Service\Checkout\MerchantAppHref $merchantAppHref
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask $quoteIdMaskResourceModel
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        MerchantAppHref $merchantAppHref,
        Validator $formKeyValidator,
        Http $request,
        QuoteIdMask $quoteIdMaskResourceModel
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->merchantAppHref = $merchantAppHref;
        $this->formKeyValidator = $formKeyValidator;
        $this->request = $request;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
    }

    /**
     * {@inheritDoc}
     */
    public function getMerchantAppHref($cartId)
    {
        $this->validateHrefRequest();
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResourceModel->load($quoteIdMask, $cartId, 'masked_id');
        $quoteId = $quoteIdMask->getQuoteId();
        return $this->merchantAppHref->get($quoteId);
    }
}
