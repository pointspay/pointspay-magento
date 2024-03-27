<?php

namespace Pointspay\Pointspay\Service\Signature;

use Pointspay\Pointspay\Service\Signature\Validator\Parse;

class Validator
{
    /**
     * @var \Pointspay\Pointspay\Service\Signature\Validator\Parse
     */
    private $headerParser;

    /**
     * @param \Pointspay\Pointspay\Service\Signature\Validator\Parse $headerParser
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        Parse $headerParser
    )
    {
        $this->headerParser = $headerParser;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validate($data)
    {
        $signatureStatus = true;
        if (!empty($data['header']['authorization'])) {
            $headerParams = $this->headerParser->parse($data['header']['authorization']);
            $signature = base64_decode($headerParams->getOauthSignature());
            $publicCertificate = openssl_get_publickey($data['request']['key_info']['certificate']);
            $bodyToProcess = $data['body'];
            $bodyToVerify = $this->castArrayToString($bodyToProcess);
            $paramsToAppend = sprintf('%s%s%s%s', $headerParams->getOauthConsumerKey(), 'SHA256withRSA', $headerParams->getOauthNonce(), $headerParams->getOauthTimestamp());
            $messageToVerify = mb_convert_encoding(sprintf('%s%s', $bodyToVerify, $paramsToAppend), 'UTF-8');
            $validationResult = openssl_verify($messageToVerify, $signature, $publicCertificate, 'sha256WithRSAEncryption');
            $signatureStatus = $validationResult === 1;
        }
        return $signatureStatus;
    }

    private function castArrayToString($body)
    {
        $body = array_filter($body);
        $this->recursiveArraySort($body);
        return mb_convert_encoding(json_encode($body,JSON_UNESCAPED_SLASHES), 'UTF-8');
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

}
