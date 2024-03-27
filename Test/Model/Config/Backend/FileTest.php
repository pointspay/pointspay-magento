<?php
namespace Pointspay\Pointspay\Test\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Test\Model\Config\Backend\FileTestSubject as File;
use Pointspay\Pointspay\Model\File\UploaderFactory as UploaderCertificateFactory;

class FileTest extends TestCase
{
    private $file;
    private $uploaderCertificateFactoryMock;
    private $uploaderMock;

    protected function setUp(): void
    {
        $this->uploaderCertificateFactoryMock = $this->createMock(UploaderCertificateFactory::class);
        $fileUploaderMock = $this->createMock(\Pointspay\Pointspay\Model\File\Uploader::class);
        $fileUploaderMock->expects($this->any())->method('setAllowedExtensions')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('addValidateCallback')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('setScope')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('setScopeId')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('setCode')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('saveCertificate')->willReturn('certificate_content');
        $this->uploaderCertificateFactoryMock->method('create')->willReturn($fileUploaderMock);

        $this->uploaderMock = $this->createMock(\Pointspay\Pointspay\Model\File\Uploader::class);

        $this->file = new File(
            $this->createMock(\Magento\Framework\Model\Context::class),
            $this->createMock(\Magento\Framework\Registry::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(TypeListInterface::class),
            $this->createMock(UploaderFactory::class),
            $this->createMock(\Magento\Config\Model\Config\Backend\File\RequestData::class),
            $this->createMock(Filesystem::class),
            $this->uploaderCertificateFactoryMock
        );
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testFileUploadSuccess()
    {
        $this->uploaderCertificateFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->uploaderMock);

        $this->uploaderMock->expects($this->any())
            ->method('saveCertificate')
            ->willReturn('file.cer');

        $this->file->setValue(['tmp_name' => 'file.cer', 'name' => 'file.cer']);
        $this->file->beforeSave();

        $this->assertEquals('certificate_content', $this->file->getValue());
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testFileUploadFailure()
    {
        $this->file->setValue(['tmp_name' => 'file.cer', 'name' => 'file.cer']);
        $this->uploaderCertificateFactoryMock = $this->createMock(UploaderCertificateFactory::class);
        $fileUploaderMock = $this->createMock(\Pointspay\Pointspay\Model\File\Uploader::class);
        $fileUploaderMock->expects($this->any())->method('setAllowedExtensions')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('addValidateCallback')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('setScope')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('setScopeId')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('setCode')->willReturnSelf();
        $fileUploaderMock->expects($this->any())->method('saveCertificate')->willReturn(null);
        $this->uploaderCertificateFactoryMock->method('create')->willReturn($fileUploaderMock);

        $this->file = new File(
            $this->createMock(\Magento\Framework\Model\Context::class),
            $this->createMock(\Magento\Framework\Registry::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(TypeListInterface::class),
            $this->createMock(UploaderFactory::class),
            $this->createMock(\Magento\Config\Model\Config\Backend\File\RequestData::class),
            $this->createMock(Filesystem::class),
            $this->uploaderCertificateFactoryMock
        );

        $this->file->beforeSave();
        $this->assertEquals(null, $this->file->getValue());
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testFileDelete()
    {
        $this->file->setValue(['delete' => 1]);
        $this->file->beforeSave();

        $this->assertEquals('', $this->file->getValue());
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testFileNotChanged()
    {
        $this->file->setValue(['value' => 'file.cer']);
        $this->file->beforeSave();

        $this->assertEquals('file.cer', $this->file->getValue());
    }
}
