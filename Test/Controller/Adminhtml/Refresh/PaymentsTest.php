<?php
namespace Pointspay\Pointspay\Test\Controller\Adminhtml\Refresh;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\FlavourKeys;
use Pointspay\Pointspay\Controller\Adminhtml\Refresh\Payments;

class PaymentsTest extends TestCase
{

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    protected function setUp() : void
    {
        $objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);

        ObjectManager::setInstance($objectManagerMock);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }
    public function testExecuteWithoutException()
    {
        $request = $this->createMock(Http::class);
        $paymentMethodUpdater = $this->createMock(\Pointspay\Pointspay\Service\PaymentMethodsUpdater::class);
        $paymentMethodUpdater->method('execute')
            ->willReturnSelf();


        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($request);

        $result = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $result->method('setData')
            ->willReturnSelf();
        $resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $resultFactory->method('create')
            ->willReturn($result);
        $context->method('getResultFactory')
            ->willReturn($resultFactory);

        /** @var Payments $download */
        $download = $this->objectManagerHelper->getObject(Payments::class, ['context' => $context, 'paymentMethodsUpdater' => $paymentMethodUpdater]);

        $download->execute();
    }
    public function testCreateCsrfValidationException()
    {
        $request = $this->createMock(Http::class);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($request);
        $paymentMethodUpdater = $this->createMock(\Pointspay\Pointspay\Service\PaymentMethodsUpdater::class);
        $paymentMethodUpdater->method('execute')
            ->willReturnSelf();
        /** @var Payments $download */
        $download = $this->objectManagerHelper->getObject(Payments::class, ['context' => $context, 'paymentMethodsUpdater' => $paymentMethodUpdater]);
        $this->assertNull($download->createCsrfValidationException($request));
    }

    public function testValidateForCsrf()
    {
        $request = $this->createMock(Http::class);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($request);
        $paymentMethodUpdater = $this->createMock(\Pointspay\Pointspay\Service\PaymentMethodsUpdater::class);
        $paymentMethodUpdater->method('execute')
            ->willReturnSelf();
        /** @var Payments $download */
        $download = $this->objectManagerHelper->getObject(Payments::class, ['context' => $context, 'paymentMethodsUpdater' => $paymentMethodUpdater]);
        $this->assertTrue($download->validateForCsrf($request));
    }
}
