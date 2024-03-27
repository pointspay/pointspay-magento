<?php

namespace Pointspay\Pointspay\Service\Api;

use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Api\Data\CheckoutRequestInterface;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Service\HTTP\AsyncClient\Request;
use Psr\Log\LoggerInterface;

abstract class AbstractApi implements CheckoutRequestInterface
{
    protected $nonce = '';

    protected $consumerKey = '';

    protected $oAuthTimestamp = '';

    protected $oAuthSignature = '';

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    protected $generalHelper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\HTTP\AsyncClientInterface
     */
    protected $asyncClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Pointspay\Pointspay\Helper\Config $generalHelper
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\HTTP\AsyncClientInterface $asyncClient
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Config $generalHelper,
        SerializerInterface $serializer,
        AsyncClientInterface $asyncClient,
        LoggerInterface $logger
    ) {
        $this->generalHelper = $generalHelper;
        $this->serializer = $serializer;
        $this->asyncClient = $asyncClient;
        $this->logger = $logger;
    }

    abstract public function execute($apiEndpoint = null, $method = Request::METHOD_POST, $arrayForApi = [], $headersForApi = []);

    public function getOauthParamsHeader()
    {
        return sprintf(
            'oauth_consumer_key="%s",oauth_signature_method="%s",oauth_nonce="%s",oauth_timestamp="%s",oauth_signature="%s"',
            $this->getOauthConsumerKey(),
            $this->getOauthSignatureMethod(),
            $this->getOauthNonce(),
            $this->getOauthTimestamp(),
            $this->getOauthSignature()
        );
    }

    public function getOauthParamsBody()
    {
        return sprintf(
            '%s%s%s%s',
            $this->getOauthConsumerKey(),
            $this->getOauthSignatureMethod(),
            $this->getOauthNonce(),
            $this->getOauthTimestamp()
        );
    }

    public function getOauthConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * @return string
     */
    public function getOauthSignatureMethod(): string
    {
        return 'SHA256withRSA';
    }

    public function getOauthNonce()
    {
        return $this->nonce;
    }

    public function getOauthTimestamp()
    {
        return $this->oAuthTimestamp;
    }

    public function setOAuthTimestamp(string $oAuthTimestamp): void
    {
        $this->oAuthTimestamp = $oAuthTimestamp;
    }

    public function getOauthSignature()
    {
        return $this->oAuthSignature;
    }

    public function setOAuthSignature(string $oAuthSignature): void
    {
        $this->oAuthSignature = $oAuthSignature;
    }

    public function getApiEndpoint($code = null)
    {
        return $this->generalHelper->getApiEndpoint($code);
    }

    public function setOauthConsumerKey(string $consumerKey): void
    {
        $this->consumerKey = $consumerKey;
    }

    /**
     * @param mixed $nonce
     */
    public function setOauthNonce($nonce): void
    {
        $this->nonce = $nonce;
    }

    protected function logError(array $array)
    {
        $this->logger->addError($this->serializer->serialize($array));
    }
}
