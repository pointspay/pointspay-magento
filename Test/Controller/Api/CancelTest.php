<?php
namespace Pointspay\Pointspay\Test\Controller\Api;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Controller\Api\Cancel;
use Pointspay\Pointspay\Service\Checkout\Service;
use Pointspay\Pointspay\Model\Quote\RestoreData;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Pointspay\Pointspay\Service\Logger\Logger;

class CancelTest extends TestCase
{
    /**
     * @var Cancel
     */
    private $controller;

    /**
     * @var MockObject|Service
     */
    private $serviceMock;

    /**
     * @var MockObject|RestoreData
     */
    private $restoreDataMock;

    /**
     * @var MockObject|ResultFactory
     */
    private $resultFactoryMock;

    /**
     * @var MockObject|ManagerInterface
     */
    private $messageManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->serviceMock = $this->getMockBuilder(Service::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->restoreDataMock = $this->getMockBuilder(RestoreData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->createMock(Http::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->controller = $objectManagerHelper->getObject(
            Cancel::class,
            [
                'request' => $this->requestMock,
                'logger' => $this->loggerMock,
                'service' => $this->serviceMock,
                'restoreData' => $this->restoreDataMock,
                'resultFactory' => $this->resultFactoryMock,
                'messageManager' => $this->messageManagerMock
            ]
        );

    }

    public function testExecuteWithValidData()
    {
        $postData = ['order_id' => '123', 'payment_id' => '456', 'status'=>'SUCCESS'];
        $content = json_encode($postData);

        $this->requestMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->serviceMock->expects($this->once())
            ->method('logPostData')
            ->with($content);

        $this->serviceMock->expects($this->once())
            ->method('restorePostData')
            ->with($content)
            ->willReturn($postData);

        $this->restoreDataMock->expects($this->once())
            ->method('cancelOrder')
            ->with($postData, 'cancel');

        $message = __('Canceling a payment.');
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')->with($message);

        $this->controller->execute();
    }

    public function testExecuteWithInvalidData()
    {
        $content = 'invalid_json_data';

        $this->requestMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->serviceMock->expects($this->once())
            ->method('logPostData')
            ->with($content);

        $this->serviceMock->expects($this->once())
            ->method('restorePostData')
            ->with($content)
            ->willReturn([]);

        $message = __('Payment cannot be canceled. Incorrect parameters.');

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($message);

        $this->controller->execute();
    }
}
