<?php
namespace Pointspay\Pointspay\Test\Service\Checkout;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Checkout\MerchantAppHref;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

class MerchantAppHrefTest extends TestCase
{
    private $quoteFactory;
    private $quoteResource;
    private $orderCollectionFactory;
    private $merchantAppHref;

    protected function setUp(): void
    {
        $this->quoteFactory = $this->createMock(QuoteFactory::class);
        $this->quoteResource = $this->createMock(Quote::class);
        $this->orderCollectionFactory = $this->createMock(CollectionFactoryInterface::class);
        $this->merchantAppHref = new MerchantAppHref($this->quoteFactory, $this->quoteResource, $this->orderCollectionFactory);
    }

    public function testGetReturnsMerchantAppHrefWhenRequestIsValid()
    {
        $quoteId = '12345';
        $expectedHref = 'http://example.com';

        $quoteModel = $this->createMock(\Magento\Quote\Model\Quote::class);
        $orderModel = $this->createMock(\Magento\Sales\Model\Order::class);
        $paymentModel = $this->createMock(\Magento\Sales\Model\Order\Payment::class);

        $this->quoteFactory->method('create')->willReturn($quoteModel);
        $this->quoteResource->method('load')->with($quoteModel, $quoteId);
        $quoteModel->method('getId')->willReturn($quoteId);

        $orderCollection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $this->orderCollectionFactory->method('create')->willReturn($orderCollection);
        $orderCollection->method('addFieldToFilter')->with('quote_id', $quoteId)->willReturnSelf();
        $orderCollection->method('addOrder')->with('entity_id', 'DESC')->willReturnSelf();
        $orderCollection->method('getFirstItem')->willReturn($orderModel);

        $orderModel->method('getId')->willReturn(1);
        $orderModel->method('getPayment')->willReturn($paymentModel);
        $paymentModel->method('getAdditionalInformation')->willReturn(['href' => $expectedHref]);

        $this->assertEquals($expectedHref, $this->merchantAppHref->get($quoteId));
    }

    public function testGetThrowsExceptionWhenQuoteNotFound()
    {
        $quoteId = '12345';

        $quoteModel = $this->createMock(\Magento\Quote\Model\Quote::class);

        $this->quoteFactory->method('create')->willReturn($quoteModel);
        $this->quoteResource->method('load')->with($quoteModel, $quoteId);
        $quoteModel->method('getId')->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Quote not found');

        $this->merchantAppHref->get($quoteId);
    }

    public function testGetThrowsExceptionWhenOrderNotFound()
    {
        $quoteId = '12345';

        $quoteModel = $this->createMock(\Magento\Quote\Model\Quote::class);
        $orderModel = $this->createMock(\Magento\Sales\Model\Order::class);

        $this->quoteFactory->method('create')->willReturn($quoteModel);
        $this->quoteResource->method('load')->with($quoteModel, $quoteId);
        $quoteModel->method('getId')->willReturn($quoteId);

        $orderCollection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $this->orderCollectionFactory->method('create')->willReturn($orderCollection);
        $orderCollection->method('addFieldToFilter')->with('quote_id', $quoteId)->willReturnSelf();
        $orderCollection->method('addOrder')->with('entity_id', 'DESC')->willReturnSelf();
        $orderCollection->method('getFirstItem')->willReturn($orderModel);

        $orderModel->method('getId')->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Order not found');

        $this->merchantAppHref->get($quoteId);
    }

    public function testGetThrowsExceptionWhenMerchantAppHrefNotFound()
    {
        $quoteId = '12345';

        $quoteModel = $this->createMock(\Magento\Quote\Model\Quote::class);
        $orderModel = $this->createMock(\Magento\Sales\Model\Order::class);
        $paymentModel = $this->createMock(\Magento\Sales\Model\Order\Payment::class);

        $this->quoteFactory->method('create')->willReturn($quoteModel);
        $this->quoteResource->method('load')->with($quoteModel, $quoteId);
        $quoteModel->method('getId')->willReturn($quoteId);

        $orderCollection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $this->orderCollectionFactory->method('create')->willReturn($orderCollection);
        $orderCollection->method('addFieldToFilter')->with('quote_id', $quoteId)->willReturnSelf();
        $orderCollection->method('addOrder')->with('entity_id', 'DESC')->willReturnSelf();
        $orderCollection->method('getFirstItem')->willReturn($orderModel);

        $orderModel->method('getId')->willReturn(1);
        $orderModel->method('getPayment')->willReturn($paymentModel);
        $paymentModel->method('getAdditionalInformation')->willReturn([]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Merchant App Href not found');

        $this->merchantAppHref->get($quoteId);
    }
}
