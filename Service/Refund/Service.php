<?php

namespace Pointspay\Pointspay\Service\Refund;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Pointspay\Pointspay\Api\Data\CheckoutServiceInterface;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Service\Checkout\VirtualCheckoutServiceFactory as CheckoutFactory;
use Psr\Log\LoggerInterface;

class Service extends \Pointspay\Pointspay\Service\Checkout\Service
{
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
     * @param CheckoutFactory $checkoutFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
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
        parent::__construct($logger, $serialize, $client, $config, $urlInterface, $checkoutFactory, $orderCollectionFactory);
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
     * @return \Pointspay\Pointspay\Service\Refund\VirtualRefundService
     */
    public function createCheckoutService(CheckoutServiceInterface $client, array $clientConfig = [])
    {
        return $this->checkoutFactory->create(['client' => $client, 'clientConfig' => $clientConfig]);
    }

}
