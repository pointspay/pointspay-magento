<?php

namespace Pointspay\Pointspay\Test\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\FlavourKeysFactory;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys\Collection;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys\CollectionFactory;
use Pointspay\Pointspay\Service\CertificateHandler;

class CertificateHandlerTest extends TestCase
{
    private $certificateHandler;

    /**
     * @magentoDbIsolation enabled
     */
    public function testCertificateHandlerReturnsExpectedValueAndCertificateExist(): void
    {
        $flavourKeysResourceModel = $this->createMock(FlavourKeys::class);
        $collectionFactory = $this->createMock(CollectionFactory::class);
        $flavourKeysFactory = $this->createMock(FlavourKeysFactory::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $flavourKeysCollection = $this->createMock(Collection::class);
        $flavourKeysModel = $this->createMock(\Pointspay\Pointspay\Model\FlavourKeys::class);
        $flavourKeysModel->method('getId')->willReturnOnConsecutiveCalls('some_id', 'some_id');

        $collectionFactory->method('create')->willReturn($flavourKeysCollection);

        $websiteFilter = [['website_id', 'website_id'],
                [
                    [ 'eq' => 0],
                    [ 'eq' => 1]
                ]];
        $flavourKeysCollection->method('addFieldToFilter')->withConsecutive(['payment_code', 'pointspay_required_settings'], $websiteFilter)->willReturn($flavourKeysCollection);

        $flavourKeysCollection->method('getFirstItem')->willReturn($flavourKeysModel);
        $this->certificateHandler = new CertificateHandler(
            $flavourKeysResourceModel,
            $collectionFactory,
            $flavourKeysFactory,
            $scopeConfig
        );
        $model = $this->certificateHandler->get('pointspay', 1);
        $id = $model->getId();
        $this->assertNotNull($id);
        $this->assertSame('some_id', $id);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCertificateHandlerReturnsExpectedValueAndCertificateNotExist(): void
    {
        $flavourKeysResourceModel = $this->createMock(FlavourKeys::class);
        $collectionFactory = $this->createMock(CollectionFactory::class);
        $flavourKeysFactory = $this->createMock(FlavourKeysFactory::class);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $encryptor = $this->createMock(EncryptorInterface::class);
        $flavourKeysCollection = $this->createMock(Collection::class);
        $flavourKeysModel = $this->createMock(\Pointspay\Pointspay\Model\FlavourKeys::class);
        $flavourKeysModel->method('setData')->willReturn($flavourKeysModel);

        $flavourKeysModel->method('setPrivateKey')->willReturn($flavourKeysModel);
        $flavourKeysModel->method('setCertificate')->willReturn($flavourKeysModel);

        $flavourKeysResourceModel->method('save')->willReturn($flavourKeysResourceModel);

        $flavourKeysModel->method('getId')->willReturnOnConsecutiveCalls(null, 'some_id');

        $flavourKeysFactory->method('create')->willReturn($flavourKeysModel);

        $collectionFactory->method('create')->willReturn($flavourKeysCollection);
        $websiteFilter = [['website_id', 'website_id'],
            [
                [ 'eq' => 0],
                [ 'eq' => 1]
            ]];
        $flavourKeysCollection->method('addFieldToFilter')->withConsecutive(['payment_code', 'pointspay_required_settings'], $websiteFilter)->willReturn($flavourKeysCollection);

        $flavourKeysCollection->method('getFirstItem')->willReturn($flavourKeysModel);

        $scopeConfig->method('getValue')->withConsecutive(
            ['web/secure/base_url', 'website', 1],
            ['general/country/default', 'website', 1],
            ['trans_email/ident_general/email', 'website', 1]
        )->willReturnOnConsecutiveCalls(
            'https://www.example.com/',
            'US',
            'info@example.com'
        );

        $this->certificateHandler = new CertificateHandler(
            $flavourKeysResourceModel,
            $collectionFactory,
            $flavourKeysFactory,
            $scopeConfig,
            $encryptor
        );
        $model = $this->certificateHandler->get('pointspay', 1);
        $id = $model->getId();
        $this->assertNotNull($id);
        $this->assertSame('some_id', $id);
    }
    /**
     * @magentoDbIsolation enabled
     */
    public function testCertificateHandlerReturnsExpectedValueAndCertificateExistWithoutWebsiteId(): void
    {
        $flavourKeysResourceModel = $this->createMock(FlavourKeys::class);
        $collectionFactory = $this->createMock(CollectionFactory::class);
        $flavourKeysFactory = $this->createMock(FlavourKeysFactory::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $encryptor = $this->createMock(EncryptorInterface::class);
        $flavourKeysCollection = $this->createMock(Collection::class);
        $flavourKeysModel = $this->createMock(\Pointspay\Pointspay\Model\FlavourKeys::class);
        $flavourKeysModel->method('getId')->willReturnOnConsecutiveCalls('some_id', 'some_id');

        $collectionFactory->method('create')->willReturn($flavourKeysCollection);

        $flavourKeysCollection->method('addFieldToFilter')->withConsecutive(['payment_code', 'pointspay_required_settings'], ['website_id', 1])->willReturn($flavourKeysCollection);

        $flavourKeysCollection->method('getFirstItem')->willReturn($flavourKeysModel);
        $this->certificateHandler = new CertificateHandler(
            $flavourKeysResourceModel,
            $collectionFactory,
            $flavourKeysFactory,
            $scopeConfig,
            $encryptor
        );
        $model = $this->certificateHandler->get('pointspay');
        $id = $model->getId();
        $this->assertNotNull($id);
        $this->assertSame('some_id', $id);
    }
    /**
     * @magentoDbIsolation enabled
     */
    public function testCertificateHandlerReturnsExpectedValueAndCertificateNotExistWithoutWebsiteId(): void
    {
        $flavourKeysResourceModel = $this->createMock(FlavourKeys::class);
        $collectionFactory = $this->createMock(CollectionFactory::class);
        $flavourKeysFactory = $this->createMock(FlavourKeysFactory::class);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $encryptor = $this->createMock(EncryptorInterface::class);
        $flavourKeysCollection = $this->createMock(Collection::class);
        $flavourKeysModel = $this->createMock(\Pointspay\Pointspay\Model\FlavourKeys::class);
        $flavourKeysModel->method('setData')->willReturn($flavourKeysModel);

        $flavourKeysModel->method('setPrivateKey')->willReturn($flavourKeysModel);
        $flavourKeysModel->method('setCertificate')->willReturn($flavourKeysModel);

        $flavourKeysResourceModel->method('save')->willReturn($flavourKeysResourceModel);

        $flavourKeysModel->method('getId')->willReturnOnConsecutiveCalls(null, 'some_id');

        $flavourKeysFactory->method('create')->willReturn($flavourKeysModel);

        $collectionFactory->method('create')->willReturn($flavourKeysCollection);

        $flavourKeysCollection->method('addFieldToFilter')->withConsecutive(
            ['payment_code', 'pointspay_required_settings'],
            ['website_id', 0]
        )->willReturn($flavourKeysCollection);

        $flavourKeysCollection->method('getFirstItem')->willReturn($flavourKeysModel);

        $scopeConfig->method('getValue')->withConsecutive(
            ['web/secure/base_url', 'website', 0],
            ['general/country/default', 'website', 0],
            ['trans_email/ident_general/email', 'website', 0]
        )->willReturnOnConsecutiveCalls(
            'https://www.example.com/',
            'US',
            'info@example.com'
        );

        $this->certificateHandler = new CertificateHandler(
            $flavourKeysResourceModel,
            $collectionFactory,
            $flavourKeysFactory,
            $scopeConfig,
            $encryptor
        );
        $model = $this->certificateHandler->get('pointspay');
        $id = $model->getId();
        $this->assertNotNull($id);
        $this->assertSame('some_id', $id);
    }

    protected function setUp(): void
    {

    }
}
