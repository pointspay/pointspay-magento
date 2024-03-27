<?php

namespace Pointspay\Pointspay\Service\Signature;

class Creator
{

    /**
     * @param array $data
     * @param array $clientConfig
     * @return string
     */
    public function create($data, $clientConfig)
    {
        $signature = '';
        $dataInString = mb_convert_encoding(json_encode($data, JSON_UNESCAPED_SLASHES), 'UTF-8');
        $dataToAppend = sprintf('%s%s%s%s', $clientConfig['oauth']['consumer_key'], 'SHA256withRSA', $clientConfig['oauth']['nonce'], $clientConfig['oauth']['timestamp']);
        $dataInString = sprintf('%s%s', $dataInString, $dataToAppend);
        $privateKey = openssl_pkey_get_private($clientConfig['key_info']['private_key']);
        openssl_sign($dataInString, $signature, $privateKey, 'sha256WithRSAEncryption');
        return base64_encode($signature);
    }
}
