<?php

namespace Pointspay\Pointspay\Service\Api\Environment;

use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Api\Data\CheckoutRequestInterface;
use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;

class Live implements \Pointspay\Pointspay\Api\Data\ApiInterface
{
    /**
     * @var CheckoutRequestInterface
     */
    private $api;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    public function __construct(
        CheckoutRequestInterface $api,
        SerializerInterface $serializer
    ) {
        $this->api = $api;
        $this->serializer = $serializer;
    }

    public function getPaymentMethods()
    {
//      if you want environment specific url that depends on the environment use the following code
//      $paymentMethodsEndpoint = sprintf('%sapi/v1/payment-methods', $this->api->getApiEndpoint());
        $url = PointspayGeneralPaymentInterface::POINTSPAY_LIVE_URL;
        $paymentMethodsEndpoint = sprintf('%sapi/v1/payment-methods', $url);
        $promise = $this->api->execute($paymentMethodsEndpoint);
        $methods = $promise->get()->getBody();
        return $this->serializer->unserialize($methods);
    }

}
