<?php

namespace Pointspay\Pointspay\Service\Api\PaymentMethods;

use GuzzleHttp\Exception\TransferException;
use Pointspay\Pointspay\Service\HTTP\AsyncClient\Request;

class GetMethods extends \Pointspay\Pointspay\Service\Api\AbstractApi
{

    /**
     * @param null $apiEndpoint
     * @param string $method
     * @param array $arrayForApi
     * @return \Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface
     */
    public function execute($apiEndpoint = null, $method = Request::METHOD_GET, $arrayForApi = [], $headersForApi = [])
    {
        $result = null;
        $body = !empty($arrayForApi) ? $this->serializer->serialize($arrayForApi) : null;
        $headersForApi = !empty($headersForApi) ? $headersForApi : [
            'X-Api-Key' => $this->generalHelper->getApiKey(),
        ];
        $apiEndpointForRequest = $apiEndpoint?:$this->getApiEndpoint();
        try {
            $result = $this->asyncClient->request(
                new Request(
                    $apiEndpointForRequest,
                    $method,
                    $headersForApi,
                    $body,
                    ['timeout' => $this->generalHelper->getRequestTimeout()]
                )
            );
        } catch (TransferException $e) {
            $this->logError([
                'endpoint' => $apiEndpoint,
                'request' => $arrayForApi,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        return $result;
    }

}
