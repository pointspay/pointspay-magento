<?php

namespace Pointspay\Pointspay\Service\Api\CartProvider;

use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Class GuestCartProvider
 *
 * @package Pointspay\Pointspay\Service\Api\CartProvider
 */
class GuestCartProvider implements CartProvider
{
    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * GuestCartProvider constructor.
     *
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        GuestCartRepositoryInterface $guestCartRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->guestCartRepository = $guestCartRepository;
        $this->cartRepository = $cartRepository;
    }

    public function getQuote(string $cartId): Quote
    {
        /** @var Quote $quote */
        $quote = $this->guestCartRepository->get($cartId);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('cartId', $cartId);
        }

        if ($quote->getCheckoutMethod() !== Onepage::METHOD_GUEST) {
            $quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            $quote->setCustomerIsGuest(true);
            $this->cartRepository->save($quote);
        }

        return $quote;
    }
}
