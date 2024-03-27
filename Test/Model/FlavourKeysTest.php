<?php
namespace Pointspay\Pointspay\Test\Model;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\FlavourKeys;

class FlavourKeysTest extends TestCase
{
    private $encryptorMock;
    private $flavourKeys;

    protected function setUp(): void
    {
        $this->encryptorMock = $this->getMockForAbstractClass(EncryptorInterface::class);

        $objectManager = new ObjectManager($this);
        $this->flavourKeys = $objectManager->getObject(
            FlavourKeys::class,
            ['encryptor' => $this->encryptorMock]
        );
    }

    public function testSetAndGetCertificate()
    {
        $certificate = 'test_certificate';
        $this->flavourKeys->setCertificate($certificate);
        $this->assertEquals($certificate, $this->flavourKeys->getCertificate());
    }

    public function testSetAndGetPrivateKey()
    {
        $privateKey = 'test_private_key';
        $this->flavourKeys->setPrivateKey($privateKey);
        $this->assertEquals($privateKey, $this->flavourKeys->getPrivateKey());
    }
}
