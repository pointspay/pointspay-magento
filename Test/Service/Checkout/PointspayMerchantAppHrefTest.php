<?php
namespace Pointspay\Pointspay\Test\Service\Checkout;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Checkout\PointspayMerchantAppHref;
use Pointspay\Pointspay\Service\Checkout\MerchantAppHref;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Request\Http;

class PointspayMerchantAppHrefTest extends TestCase
{
    private $merchantAppHref;
    private $validator;
    private $request;
    private $object;

    protected function setUp(): void
    {
        $this->merchantAppHref = $this->createMock(MerchantAppHref::class);
        $this->validator = $this->createMock(Validator::class);
        $this->request = $this->createMock(Http::class);
        $this->object = new PointspayMerchantAppHref($this->merchantAppHref, $this->validator, $this->request);
    }

    public function testValidateHrefRequestReturnsMerchantAppHrefWhenRequestIsValid()
    {
        $cartId = '12345';
        $expectedHref = 'http://example.com';

        $this->request->method('isAjax')->willReturn(true);
        $this->validator->method('validate')->with($this->request)->willReturn(true);
        $this->merchantAppHref->method('get')->with($cartId)->willReturn($expectedHref);

        $this->assertEquals($expectedHref, $this->object->getMerchantAppHref($cartId));
    }

    public function testValidateHrefRequestThrowsExceptionWhenRequestIsNotAjax()
    {
        $this->request->method('isAjax')->willReturn(false);
        $this->expectException(\Magento\Framework\Webapi\Exception::class);

        $this->object->getMerchantAppHref('12345');
    }

    public function testValidateHrefRequestThrowsExceptionWhenFormKeyIsInvalid()
    {
        $this->request->method('isAjax')->willReturn(true);
        $this->validator->method('validate')->with($this->request)->willReturn(false);
        $this->expectException(\Magento\Framework\Webapi\Exception::class);

        $this->object->getMerchantAppHref('12345');
    }
}
