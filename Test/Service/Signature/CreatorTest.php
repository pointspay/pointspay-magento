<?php

namespace Pointspay\Pointspay\Test\Service\Signature;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Signature\Creator;

class CreatorTest extends TestCase
{
    private $testPrivateKey = '-----BEGIN PRIVATE KEY-----
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
uus9lJMu+vWTKX1PqLFfSeMrpN604A==
-----END PRIVATE KEY-----
';

    /**
     * @var \Pointspay\Pointspay\Service\Signature\Creator
     */
    private Creator $creator;

    protected function setUp(): void
    {
        $this->creator = new Creator();
    }

    public function testCreateSignatureWithValidData()
    {
        $data = ['key' => 'value'];
        $clientConfig = [
            'oauth' => [
                'consumer_key' => 'key',
                'nonce' => 'nonce',
                'timestamp' => 'timestamp'
            ],
            'key_info' => [
                'private_key' => $this->testPrivateKey
            ]
        ];

        $result = $this->creator->create($data, $clientConfig);

        $this->assertIsString($result);
    }

    public function testCreateSignatureWithEmptyData()
    {
        $data = [];
        $clientConfig = [
            'oauth' => [
                'consumer_key' => 'key',
                'nonce' => 'nonce',
                'timestamp' => 'timestamp'
            ],
            'key_info' => [
                'private_key' => $this->testPrivateKey
            ]
        ];

        $result = $this->creator->create($data, $clientConfig);

        $this->assertIsString($result);
    }

    public function testCreateSignatureWithInvalidPrivateKey()
    {
        $data = ['key' => 'value'];
        $clientConfig = [
            'oauth' => [
                'consumer_key' => 'key',
                'nonce' => 'nonce',
                'timestamp' => 'timestamp'
            ],
            'key_info' => [
                'private_key' => ''
            ]
        ];
$correctException = false;
        try {
            $this->creator->create($data, $clientConfig);
        }catch (\Throwable $exception){
            $message = $exception->getMessage();
            if (strpos($message, 'Supplied key param cannot be coerced into a private key') !==false){
                $correctException = true;
            }else{
                $correctException = false;
            }
        }
        $this->assertTrue($correctException);
    }
}
