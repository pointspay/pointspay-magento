<?php

namespace Pointspay\Pointspay\Test\Model\Method\FakeBuilder\AdditionalData;
use Pointspay\Pointspay\Api\Data\ApiInterface;

class DynamicUrlsDataBuilder extends \Pointspay\Pointspay\Gateway\Request\AdditionalData\DynamicUrlsDataBuilder
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $baseUrl = 'https://example.com/';
        $request['body']['additional_data']['dynamic_urls']['success'] = $baseUrl . ApiInterface::POINTSPAY_SUCCESS_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['cancel'] = $baseUrl . ApiInterface::POINTSPAY_CANCEL_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['failure'] = $baseUrl . ApiInterface::POINTSPAY_FAIL_SUFFIX;
        $request['body']['additional_data']['dynamic_urls']['ipn'] = $baseUrl . ApiInterface::REST_IPN_SUFFIX;
        return $request;
    }
}
