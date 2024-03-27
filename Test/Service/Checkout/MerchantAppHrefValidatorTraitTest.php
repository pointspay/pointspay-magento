<?php
namespace Pointspay\Pointspay\Test\Service\Checkout;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Checkout\MerchantAppHrefValidatorTrait;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;

class MerchantAppHrefValidatorTraitTest extends TestCase
{
    private $validator;
    private $request;
    private $object;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(Validator::class);
        $this->request = $this->createMock(Http::class);
        $this->object = $this->getObjectForTrait(
            MerchantAppHrefValidatorTrait::class,
            [$this->validator, $this->request]);
        $this->object->__construct($this->validator, $this->request);
    }

    public function testValidateHrefRequestReturnsTrueWhenRequestIsValid()
    {
        $this->request->method('isAjax')->willReturn(true);
        $this->validator->method('validate')->with($this->request)->willReturn(true);

        $this->assertTrue($this->object->validateHrefRequest());
    }

    public function testValidateHrefRequestThrowsExceptionWhenRequestIsNotAjax()
    {
        $this->request->method('isAjax')->willReturn(false);
        $this->expectException(\Magento\Framework\Webapi\Exception::class);

        $this->object->validateHrefRequest();
    }

    public function testValidateHrefRequestThrowsExceptionWhenFormKeyIsInvalid()
    {
        $this->request->method('isAjax')->willReturn(true);
        $this->validator->method('validate')->with($this->request)->willReturn(false);
        $this->expectException(\Magento\Framework\Webapi\Exception::class);

        $this->object->validateHrefRequest();
    }
}
