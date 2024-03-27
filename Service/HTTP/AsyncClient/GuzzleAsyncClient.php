<?php

namespace Pointspay\Pointspay\Service\HTTP\AsyncClient;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Magento\Framework\HTTP\AsyncClient\GuzzleWrapDeferred;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
class GuzzleAsyncClient extends \Magento\Framework\HTTP\AsyncClient\GuzzleAsyncClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    public function __construct(
        Client $client
    ) {
        parent::__construct($client);
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function request(Request $request): HttpResponseDeferredInterface
    {
        $options = $request->getOptions() ?? [];
        $options[RequestOptions::HEADERS] = $request->getHeaders();
        if ($request->getBody() !== null) {
            $options[RequestOptions::BODY] = $request->getBody();
        }

        return new GuzzleWrapDeferred(
            $this->client->requestAsync(
                $request->getMethod(),
                $request->getUrl(),
                $options
            )
        );
    }
}
