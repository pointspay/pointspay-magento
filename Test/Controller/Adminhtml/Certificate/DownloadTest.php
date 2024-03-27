<?php
namespace Pointspay\Pointspay\Test\Controller\Adminhtml\Certificate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\FlavourKeys;
use Pointspay\Pointspay\Service\CertificateHandler;

class DownloadTest extends TestCase
{
    private $download;
    private $request;
    private $certificateHandler;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->certificateHandler = $this->createMock(CertificateHandler::class);
    }

    public function testExecuteWithValidParameters()
    {
        $request = $this->createMock(Http::class);
        $request->method('getParam')
            ->withConsecutive(['scope_id'], ['payment_method_code'])
            ->willReturnOnConsecutiveCalls(1, 'test_payment_method');

        $flavourMock = $this->createMock(FlavourKeys::class);
        $flavourMock->expects($this->once())
            ->method('getCertificate')
            ->willReturn('test_certificate_content');
        $this->certificateHandler->method('get')
            ->willReturn($flavourMock);

        $this->expectOutputString('test_certificate_content');

        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($request);

        /** @var DownloadTestSubject $download */
        $download = $this->objectManagerHelper->getObject(DownloadTestSubject::class, ['context' => $context, 'certificateHandler' => $this->certificateHandler]);

        $download->execute();
    }

    public function testExecuteWithMissingPaymentMethodCode()
    {
        $request = $this->createMock(Http::class);
        $request->method('getParam')
            ->withConsecutive(['scope_id'], ['payment_method_code'])
            ->willReturnOnConsecutiveCalls(1, '');

        $flavourMock = $this->createMock(FlavourKeys::class);
        $flavourMock->expects($this->once())
            ->method('getCertificate')
            ->willReturn('');
        $this->certificateHandler->method('get')
            ->willReturn($flavourMock);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($request);

        /** @var DownloadTestSubject $download */
        $download = $this->objectManagerHelper->getObject(DownloadTestSubject::class, ['context' => $context, 'certificateHandler' => $this->certificateHandler]);
        $this->expectOutputString('');

        $download->execute();
    }

    public function testExecuteWithInvalidScopeId()
    {
        $request = $this->createMock(Http::class);
        $request->method('getParam')
            ->withConsecutive(['scope_id'], ['payment_method_code'])
            ->willReturnOnConsecutiveCalls('', '');

        $flavourMock = $this->createMock(FlavourKeys::class);
        $flavourMock->expects($this->once())
            ->method('getCertificate')
            ->willReturn('');
        $this->certificateHandler->method('get')
            ->willReturn($flavourMock);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($request);

        /** @var DownloadTestSubject $download */
        $download = $this->objectManagerHelper->getObject(DownloadTestSubject::class, ['context' => $context, 'certificateHandler' => $this->certificateHandler]);
        $this->expectOutputString('');

        $download->execute();
    }

    public function testCreateCsrfValidationException()
    {
        $request = $this->createMock(Http::class);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($request);
        /** @var DownloadTestSubject $download */
        $download = $this->objectManagerHelper->getObject(DownloadTestSubject::class, ['context' => $context, 'certificateHandler' => $this->certificateHandler]);
        $this->assertNull($download->createCsrfValidationException($request));
    }

    public function testValidateForCsrf()
    {
        $request = $this->createMock(Http::class);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($request);
        /** @var DownloadTestSubject $download */
        $download = $this->objectManagerHelper->getObject(DownloadTestSubject::class, ['context' => $context, 'certificateHandler' => $this->certificateHandler]);
        $this->assertTrue($download->validateForCsrf($request));
    }
}
