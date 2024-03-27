<?php
declare(strict_types=1);

namespace Pointspay\Pointspay\Test\Controller\Api;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Controller\Api\Ipn;
use Pointspay\Pointspay\Service\Checkout\Service;
use Pointspay\Pointspay\Api\IpnInterface;

class IpnTest extends TestCase
{
    /**
     * @var Ipn
     */
    private $controller;

    /**
     * @var MockObject|Service
     */
    private $serviceMock;

    /**
     * @var MockObject|IpnInterface
     */
    private $ipnModelMock;

    /** @var RequestInterface|MockObject */
    private $requestMock;


    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);

        $this->serviceMock = $this->getMockBuilder(Service::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ipnModelMock = $this->getMockBuilder(IpnInterface::class)
            ->getMockForAbstractClass();
        $objectManagerHelper = new ObjectManager($this);
        $this->controller = $objectManagerHelper->getObject(
            Ipn::class,
            [
                'request' => $this->requestMock,
                'service' => $this->serviceMock,
                'ipnModel' => $this->ipnModelMock,
            ]
        );
    }

    /**
     * Test execute() method for PUT request with valid IPN data
     */
    public function testExecuteForValidPutRequest()
    {
        $ipnData = [
            IpnInterface::ORDER_ID => '100000002',
            IpnInterface::PAYMENT_ID => '123456789',
            IpnInterface::STATUS => 'SUCCESS'
        ];

        $this->requestMock->expects($this->once())->method('isPut')->willReturn(true);
        $this->requestMock->expects($this->once())->method('getContent')->willReturn(json_encode($ipnData));


        $this->ipnModelMock->expects($this->once())
            ->method('processIpnRequest')
            ->with($ipnData);


        // Inject the mocked Request object into the controller
        $this->controller->execute();
    }

    /**
     * Test execute() method for non-PUT request
     */
    public function testExecuteForNonPutRequest()
    {
        $this->ipnModelMock->expects($this->never())
            ->method('processIpnRequest');


        $requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->method('isPut')
            ->willReturn(false);

        $this->controller->dispatch($requestMock);
    }

}
