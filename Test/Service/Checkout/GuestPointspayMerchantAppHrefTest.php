<?php

namespace Pointspay\Pointspay\Test\Service\Checkout;

use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Checkout\GuestPointspayMerchantAppHref;
use Pointspay\Pointspay\Service\Checkout\MerchantAppHref;

class GuestPointspayMerchantAppHrefTest extends TestCase
{
    private $guestPointspayMerchantAppHref;

    private $quoteIdMaskFactory;

    private $merchantAppHref;

    private $formKeyValidator;

    private $request;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask|(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteIdMaskResource;

    protected function setUp(): void
    {
        $this->quoteIdMaskFactory = $this->createMock(QuoteIdMaskFactory::class);
        $this->merchantAppHref = $this->createMock(MerchantAppHref::class);
        $this->formKeyValidator = $this->createMock(Validator::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->quoteIdMaskResource = $this->createMock(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask::class);

        $this->guestPointspayMerchantAppHref = new GuestPointspayMerchantAppHref(
            $this->quoteIdMaskFactory,
            $this->merchantAppHref,
            $this->formKeyValidator,
            $this->request,
            $this->quoteIdMaskResource
        );
    }

    public function testGuestPointspayMerchantAppHrefReturnsMerchantAppHref()
    {
        $cartId = 'test_cart_id';
        $quoteId = 'test_quote_id';
        $merchantAppHref = 'test_merchant_app_href';

        $quoteIdMask = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuoteId'])
            ->getMock();
        $quoteIdMask->method('getQuoteId')->willReturn($quoteId);
        $quoteIdMaskResource = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteIdMaskResource->expects($this->any())->method('load')->willReturn($quoteIdMask);

        $this->quoteIdMaskFactory->method('create')->willReturn($quoteIdMask);
        $this->merchantAppHref->method('get')->with($quoteId)->willReturn($merchantAppHref);

        $this->request->expects($this->any())->method('isAjax')->willReturn(true);
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);

        $this->guestPointspayMerchantAppHref = new GuestPointspayMerchantAppHref(
            $this->quoteIdMaskFactory,
            $this->merchantAppHref,
            $this->formKeyValidator,
            $this->request,
            $this->quoteIdMaskResource
        );
        $this->assertEquals($merchantAppHref, $this->guestPointspayMerchantAppHref->getMerchantAppHref($cartId));
    }

    public function testGuestPointspayMerchantAppHrefReturnsExceptionBecauseFormKeyIsAbsent()
    {
        $cartId = 'test_cart_id';
        $quoteId = 'test_quote_id';
        $merchantAppHref = 'test_merchant_app_href';

        $quoteIdMask = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuoteId'])
            ->getMock();
        $quoteIdMask->method('getQuoteId')->willReturn($quoteId);

        $this->quoteIdMaskFactory->method('create')->willReturn($quoteIdMask);
        $this->merchantAppHref->method('get')->with($quoteId)->willReturn($merchantAppHref);
        $this->expectException(\Magento\Framework\Webapi\Exception::class);
        $this->guestPointspayMerchantAppHref->getMerchantAppHref($cartId);
    }

    public function testGuestPointspayMerchantAppHrefThrowsExceptionWhenCartIdNotFound()
    {
        $cartId = 'test_cart_id';
        $quoteIdMask = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuoteId'])
            ->getMock();
        $quoteIdMask->method('getQuoteId')->willReturn(null);

        $this->quoteIdMaskFactory->method('create')->willReturn($quoteIdMask);

        $this->expectException(\Exception::class);

        $this->guestPointspayMerchantAppHref->getMerchantAppHref($cartId);
    }
}
