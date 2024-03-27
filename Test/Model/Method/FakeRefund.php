<?php

namespace Pointspay\Pointspay\Test\Model\Method;

use Pointspay\Pointspay\Test\Model\Method\FakeGetRefund as CheckoutRequestInterface;
use Pointspay\Pointspay\Service\Api\Refund\Refund;
use Pointspay\Pointspay\Service\Signature\Creator;

class FakeRefund extends Refund
{
    /**
     * @param \Pointspay\Pointspay\Api\Data\CheckoutRequestInterface $api
     * @param \Pointspay\Pointspay\Service\Signature\Creator $signatureCreator
     */
    public function __construct(
        CheckoutRequestInterface $api,
        Creator $signatureCreator
    ) {
        $this->api = $api;
        $this->signatureCreator = $signatureCreator;
        parent::__construct($api, $signatureCreator);
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
            $this->api->getApiEndpoint($this->getClientConfig()['payment_code']) . 'api/v1/refunds',
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
