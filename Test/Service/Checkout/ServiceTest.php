<?php
namespace Pointspay\Pointspay\Test\Service\Checkout;

use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Checkout\Service;
use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Api\Data\CheckoutServiceInterface;
use Pointspay\Pointspay\Service\Checkout\VirtualCheckoutServiceFactory;
use Pointspay\Pointspay\Helper\Config;
use Psr\Log\LoggerInterface;

class ServiceTest extends TestCase {
    private $service;
    private $logger;
    private $serialize;
    private $client;
    private $config;
    private $checkoutFactory;

    /**
     * @var \Magento\Framework\UrlInterface|(\Magento\Framework\UrlInterface&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Framework\UrlInterface&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $url;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory|(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionFactory;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(\Pointspay\Pointspay\Service\Logger\Logger::class);
        $this->serialize = $this->createMock(SerializerInterface::class);
        $this->client = $this->createMock(CheckoutServiceInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->checkoutFactory = $this->createMock(VirtualCheckoutServiceFactory::class);
        $this->url = $this->createMock( UrlInterface::class);
        $this->collectionFactory = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class);
        $this->service = new Service(
            $this->logger,
            $this->serialize,
            $this->client,
            $this->config,
            $this->url,
            $this->checkoutFactory,
            $this->collectionFactory
        );
    }

    public function testInitializeClientReturnsClient()
    {
        $this->assertSame($this->client, $this->service->initializeClient());
    }

    public function testCreateCheckoutServiceReturnsCheckoutService()
    {
        $clientConfig = ['key' => 'value'];
        $checkoutService = $this->createMock(\Pointspay\Pointspay\Service\Checkout\VirtualCheckoutService::class);

        $this->checkoutFactory->method('create')->with(['client' => $this->client, 'clientConfig' => $clientConfig])->willReturn($checkoutService);

        $this->assertSame($checkoutService, $this->service->createCheckoutService($this->client, $clientConfig));
    }

    public function testLogRequestLogsRequest()
    {
        $request = ['key' => 'value'];
        $string = 'string';

        $this->serialize->method('serialize')->with($request)->willReturn(json_encode($request));
        $this->config->method('getDebugMode')->willReturn(true);

        $this->logger->expects($this->any())->method('addRequest');

        $this->service->logRequest($string, $request);
    }

    public function testLogResponseLogsResponse()
    {
        $response = ['key' => 'value'];

        $this->serialize->method('serialize')->with($response)->willReturn(json_encode($response));
        $this->config->method('getDebugMode')->willReturn(true);

        $this->logger->expects($this->any())->method('addResult');

        $this->service->logResponse('test', $response);
    }
    public function testLogResponseLogsResponseNoMessage()
    {
        $response = ['key' => 'value'];

        $this->serialize->method('serialize')->with($response)->willReturn(json_encode($response));
        $this->config->method('getDebugMode')->willReturn(true);

        $this->logger->expects($this->any())->method('addResult');

        $this->service->logResponse(null, $response);
    }

    public function testLogExceptionLogsException()
    {
        $string = 'string';

        $this->config->method('getDebugMode')->willReturn(true);

        $this->logger->expects($this->once())->method('addCritical');

        $this->service->logException($string);
    }

    public function testLogPostDataLogsPostData()
    {
        $string = 'string';

        $this->config->method('getDebugMode')->willReturn(true);

        $this->logger->expects($this->once())->method('addResult');

        $this->service->logPostData($string);
    }

    public function testRestorePostDataReturnsOutput()
    {
        $data = 'order_id=12345';

        $this->assertSame(['order_id' => '12345'], $this->service->restorePostData($data));
    }

    public function testRestorePostDataReturnsFalseWhenOutputIsEmpty()
    {
        $data = 'key=value';

        $this->assertFalse($this->service->restorePostData($data));
    }

    public function testGetCustomCancelUrlWithValidData()
    {
        $postData = ['order_id' => '123', 'payment_id' => '456','redirect_to'=>'http://example.com/checkout/cart'];
        $urlParams = http_build_query($postData);
        $expectedUrl = 'http://example.com/cancel?'.$urlParams;

        $this->config->method('getCancelUrl')->willReturn('http://example.com/cancel');
        $this->url->method('getUrl')->willReturn('http://example.com/checkout/cart');

        $collection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $collection->expects($this->any())->method('addFieldToFilter')->willReturn($collection);
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $collection->expects($this->any())->method('getFirstItem')->willReturn($order);
        $this->collectionFactory->method('create')->willReturn($collection);

        $this->service = new Service(
            $this->logger,
            $this->serialize,
            $this->client,
            $this->config,
            $this->url,
            $this->checkoutFactory,
            $this->collectionFactory
        );

        $result = $this->service->getCustomCancelUrl($postData);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetCustomCancelUrlWithEmptyData()
    {
        $result = $this->service->getCustomCancelUrl([]);

        $this->assertFalse($result);
    }

    public function testGetCustomCancelUrlWithIncompleteData()
    {
        $postData = ['order_id' => '123'];

        $result = $this->service->getCustomCancelUrl($postData);

        $this->assertFalse($result);
    }
    public function testGetCustomCancelUrlWithVeryLastFalseResult()
    {
        $postData = ['order_id' => '123', 'payment_id' => '456'];
        $this->config->expects($this->any())->method('getCancelUrl')->willReturn(false);

        $collection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $collection->expects($this->any())->method('addFieldToFilter')->willReturn($collection);
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $collection->expects($this->any())->method('getFirstItem')->willReturn($order);
        $this->collectionFactory->method('create')->willReturn($collection);

        $this->service = new Service(
            $this->logger,
            $this->serialize,
            $this->client,
            $this->config,
            $this->url,
            $this->checkoutFactory,
            $this->collectionFactory
        );
        $result = $this->service->getCustomCancelUrl($postData);

        $this->assertFalse($result);
    }
}
