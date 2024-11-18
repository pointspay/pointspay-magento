<?php

namespace Pointspay\Pointspay\Service\Checkout;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Pointspay\Pointspay\Api\Data\CheckoutServiceInterface;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Service\Checkout\VirtualCheckoutServiceFactory as CheckoutFactory;
use Psr\Log\LoggerInterface;

class Service
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serialize;

    /**
     * @var \Pointspay\Pointspay\Service\Checkout\VirtualCheckoutServiceFactory
     */
    protected $checkoutFactory;

    /**
     * @var \Pointspay\Pointspay\Api\Data\CheckoutServiceInterface
     */
    protected $client;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @param LoggerInterface $logger
     * @param SerializerInterface $serialize
     * @param CheckoutServiceInterface $client
     * @param Config $config
     * @param UrlInterface $urlInterface
     * @param VirtualCheckoutServiceFactory $checkoutFactory
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serialize,
        CheckoutServiceInterface $client,
        Config $config,
        UrlInterface $urlInterface,
        CheckoutFactory $checkoutFactory,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->logger = $logger;
        $this->serialize = $serialize;
        $this->checkoutFactory = $checkoutFactory;
        $this->client = $client;
        $this->config = $config;
        $this->urlInterface = $urlInterface;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @return CheckoutServiceInterface
     */
    public function initializeClient()
    {
        return $this->client;
    }

    /**
     * @param CheckoutServiceInterface $client
     * @param array $clientConfig
     * @return \Pointspay\Pointspay\Service\Checkout\VirtualCheckoutService
     */
    public function createCheckoutService(CheckoutServiceInterface $client, array $clientConfig = [])
    {
        return $this->checkoutFactory->create(['client' => $client, 'clientConfig' => $clientConfig]);
    }

    public function logRequest(string $string, array $request)
    {
        $this->logger->addRequest($string, $request);
    }

    public function logResponse($message, array $response)
    {
        if (empty($message)) {
            $message = 'Response';
        }
        $this->logger->addResult($message, $response);
    }

    /**
     * @param string $string
     * @param array $context
     * @return void
     */
    public function logException(string $string, $context = []): void
    {
        $this->logger->addCritical('Critical: ' . $string, $context);
    }

    /**
     * Logging of incoming post data
     *
     * @param string $string
     * @return void
     */
    public function logPostData(string $string): void
    {
        $this->logger->addResult('Post Data: ' . $string);
    }

    /**
     * @param string $data
     * @return array|false
     */
    public function restorePostData(string $data)
    {
        parse_str($data, $output);
        return (empty($output) || !isset($output['order_id'])) ? false : $output;
    }

    /**
     * @param array $postData
     * @return false|string
     */
    public function getCustomCancelUrl(array $postData = [])
    {
        if (empty($postData) || !isset($postData['order_id']) || !isset($postData['payment_id'])) {
            return false;
        }
        $collection = $this->orderCollectionFactory->create();

        $collection->addFieldToFilter('increment_id', $postData['order_id']);
        $item = $collection->getFirstItem();
        $paymentCode = null;
        if ($item->getId()) {
            $paymentCode = $item->getPayment()->getAdditionalInformation('pointspay_flavor');
        }
        if ($customCancelUrl = $this->config->getCancelUrl($paymentCode)) {
            $params['order_id'] = $postData['order_id'];
            $params['payment_id'] = $postData['payment_id'];
            $params['redirect_to'] = $this->urlInterface->getUrl('checkout/cart');
            return $customCancelUrl . '?' . http_build_query($params);
        }
        return false;
    }

}
