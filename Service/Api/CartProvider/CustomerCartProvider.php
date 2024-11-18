<?php

namespace Pointspay\Pointspay\Service\Api\CartProvider;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Class CustomerCartProvider
 *
 * @package Pointspay\Pointspay\Service\Api\CartProvider
 */
class CustomerCartProvider implements CartProvider
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteResotory;

    /**
     * CustomerCartProvider constructor.
     *
     * @param CartRepositoryInterface $quoteResotory
     */
    public function __construct(CartRepositoryInterface $quoteResotory)
    {
        $this->quoteResotory = $quoteResotory;
    }

    public function getQuote(string $cartId): Quote
    {
        /** @var Quote $quote */
        $quote = $this->quoteResotory->getActive($cartId);

        if ($quote->getCheckoutMethod()) {
            $quote->setCheckoutMethod('');
            $quote->setCustomerIsGuest(false);
            $this->quoteResotory->save($quote);
        }

        return $quote;
    }
}
