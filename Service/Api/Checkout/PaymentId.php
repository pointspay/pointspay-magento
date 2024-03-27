<?php

namespace Pointspay\Pointspay\Service\Api\Checkout;

use Pointspay\Pointspay\Api\Data\CheckoutRequestInterface;
use Pointspay\Pointspay\Service\Signature\Creator;

class PaymentId implements \Pointspay\Pointspay\Api\Data\CheckoutServiceInterface
{
    /**
     * @var \Pointspay\Pointspay\Api\Data\CheckoutRequestInterface
     */
    private $api;

    private $clientConfig = [];

    /**
     * @var \Pointspay\Pointspay\Service\Signature\Creator
     */
    private $signatureCreator;

    /**
     * @param \Pointspay\Pointspay\Api\Data\CheckoutRequestInterface $api
     * @param \Pointspay\Pointspay\Service\Signature\Creator $signatureCreator
     */
    public function __construct(
        CheckoutRequestInterface $api,
        Creator $signatureCreator
    )
    {
        $this->api = $api;
        $this->signatureCreator = $signatureCreator;
    }

    /**
     * @inheritDoc
     */
    public function process($data)
    {
        $this->recursiveArraySort($data);
        $oAuthSignature = $this->signatureCreator->create($data, $this->getClientConfig());
        $this->api->setOAuthSignature($oAuthSignature);
        $this->api->setOauthConsumerKey($this->getClientConfig()['oauth']['consumer_key']);
        $this->api->setOauthNonce($this->getClientConfig()['oauth']['nonce']);
        $this->api->setOauthTimestamp($this->getClientConfig()['oauth']['timestamp']);
        $response = $this->api->execute(
            $this->api->getApiEndpoint($this->getClientConfig()['payment_code']).'api/v1/payments',
            'POST',
            $data
        );
        return $response;
    }

    private function recursiveArraySort(&$array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
               $this->recursiveArraySort($value);
            }
        }
        ksort($array);
    }

    public function getClientConfig()
    {
        return $this->clientConfig;
    }

    public function setClientConfig(array $clientConfig): void
    {
        $this->clientConfig = $clientConfig;
    }
}
