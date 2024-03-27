<?php

namespace Pointspay\Pointspay\Service\Api\Checkout;

use GuzzleHttp\Exception\TransferException;
use Pointspay\Pointspay\Service\HTTP\AsyncClient\Request;

class GetPaymentId extends \Pointspay\Pointspay\Service\Api\AbstractApi
{

    /**
     * @param null $apiEndpoint
     * @param string $method
     * @param array $arrayForApi
     * @return \Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface
     */
    public function execute($apiEndpoint = null, $method = Request::METHOD_POST, $arrayForApi = [], $headersForApi = [])
    {
        $result = null;
        $body = !empty($arrayForApi) ? mb_convert_encoding(json_encode($arrayForApi, JSON_UNESCAPED_SLASHES), 'UTF-8') : null;
        $apiEndpointForRequest = $apiEndpoint ?: $this->getApiEndpoint();
        $headersForApi = !empty($headersForApi) ? $headersForApi : [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf("Oauth %s", $this->getOauthParamsHeader())
        ];

        try {
            // if you have worries that header is incorrect you can use makeCurlRequest method to check it
            //            $result = $this->makeCurlRequest($apiEndpointForRequest, $body, $headersForApi);
            $result = $this->asyncClient->request(
                new Request(
                    $apiEndpointForRequest,
                    $method,
                    $headersForApi,
                    sprintf('%s%s', $body, $this->getOauthParamsBody()),
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

    /**
     *
     * This method exist only to make sure that pure request is working
     *
     * @param $apiEndpoint
     * @param $body
     * @param $headersForApi
     * @return bool|string
     */
    public function makeCurlRequest($apiEndpoint, $body, $headersForApi)
    {
        $processedHeaders = [];
        foreach ($headersForApi as $key => $value) {
            $processedHeaders[] = sprintf("%s: %s", $key, $value);
        }
        $bodyWithOauth = sprintf('%s%s', $body, $this->getOauthParamsBody());
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyWithOauth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $processedHeaders);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
