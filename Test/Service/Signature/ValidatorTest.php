<?php

namespace Pointspay\Pointspay\Test\Service\Signature;

use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Signature\Validator;
use Pointspay\Pointspay\Service\Signature\Validator\Parse;

class ValidatorTest extends TestCase
{
    private $validator;
    private $headerParser;

    protected function setUp(): void
    {
        $this->headerParser = $this->createMock(Parse::class);
        $this->validator = new Validator($this->headerParser);
    }

    public function testValidationSucceedsWithValidData()
    {
        $private_key_res = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $details = openssl_pkey_get_details($private_key_res);
        $public_key_res = openssl_pkey_get_public($details['key']);

        $data = [
            'header' => ['authorization' => 'OAuth key="value"'],
            'request' => ['key_info' => ['certificate' => $public_key_res]],
            'body' => ['key' => 'value']
        ];

        $dataInString = mb_convert_encoding(json_encode($data['body'], JSON_UNESCAPED_SLASHES), 'UTF-8');
        $dataInString .= sprintf('%s%s%s%s', 'key', 'SHA256withRSA', 'nonce', 'timestamp');

        openssl_sign($dataInString, $signature, $private_key_res, "sha256WithRSAEncryption");

        $this->headerParser->method('parse')->willReturn(new DataObject(['oauth_signature' => base64_encode($signature), 'oauth_consumer_key' => 'key', 'oauth_nonce' => 'nonce', 'oauth_timestamp' => 'timestamp']));

        $this->assertTrue($this->validator->validate($data));
    }
    public function testValidationSucceedsWithValidDataRecursively()
    {
        $private_key_res = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $details = openssl_pkey_get_details($private_key_res);
        $public_key_res = openssl_pkey_get_public($details['key']);

        $data = [
            'header' => ['authorization' => 'OAuth key="value"'],
            'request' => ['key_info' => ['certificate' => $public_key_res]],
            'body' => ['key1' => ['key2' => 'value']]
        ];

        $dataInString = mb_convert_encoding(json_encode($data['body'], JSON_UNESCAPED_SLASHES), 'UTF-8');
        $dataInString .= sprintf('%s%s%s%s', 'key', 'SHA256withRSA', 'nonce', 'timestamp');

        openssl_sign($dataInString, $signature, $private_key_res, "sha256WithRSAEncryption");

        $this->headerParser->method('parse')->willReturn(new DataObject(['oauth_signature' => base64_encode($signature), 'oauth_consumer_key' => 'key', 'oauth_nonce' => 'nonce', 'oauth_timestamp' => 'timestamp']));

        $this->assertTrue($this->validator->validate($data));
    }

    public function testValidationFailsWithInvalidData()
    {
        $private_key_res = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $details = openssl_pkey_get_details($private_key_res);
        $public_key_res = openssl_pkey_get_public($details['key']);

        $data = [
            'header' => ['authorization' => 'OAuth key="value"'],
            'request' => ['key_info' => ['certificate' => $public_key_res]],
            'body' => ['key' => 'value']
        ];

        $this->headerParser->method('parse')->willReturn(new DataObject(['oauth_signature' => base64_encode('invalid_signature'), 'oauth_consumer_key' => 'key', 'oauth_nonce' => 'nonce', 'oauth_timestamp' => 'timestamp']));

        $this->assertFalse($this->validator->validate($data));
    }

}
