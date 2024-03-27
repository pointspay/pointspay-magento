<?php

namespace Pointspay\Pointspay\Test\Model\File;

use DomainException;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Validator\Image;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\File\Uploader;

class UploaderTest extends TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testUploaderSavesCertificateSuccessfully()
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $writeFactoryMock = $this->createMock(WriteFactory::class);
        $writeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->createMock(Write::class));

        $filesystem = $this->objectManagerHelper->getObject(
            Filesystem::class,
            ['writeFactory' => $writeFactoryMock]
        );

        $imageMock = $this->createMock(Image::class);
        $imageMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);
        $directoryListMock = $this->createMock(DirectoryList::class);
        $targetDirectoryMock = $this->createMock(TargetDirectory::class);
        $mimeMock = $this->createMock(Mime::class);
        $driverPoolMock = $this->createMock(DriverPool::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->withConsecutive(
                [Filesystem::class],
                [DirectoryList::class],
                [TargetDirectory::class],
                [Filesystem::class],
                [Mime::class],
                [DriverPool::class],
                [Image::class]
            )
            ->willReturnOnConsecutiveCalls(
                $filesystem,
                $directoryListMock,
                $targetDirectoryMock,
                $filesystem,
                $mimeMock,
                $driverPoolMock,
                $imageMock
            );
        ObjectManager::setInstance($objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $fileId = 'fileId';
        $coreFileStorageDb = $this->createMock(Database::class);
        $coreFileStorage = $this->createMock(Storage::class);
        $validator = $this->createMock(NotProtectedExtension::class);
        $configWriter = $this->createMock(WriterInterface::class);
        $pathHere = realpath(__DIR__);
        $pathForFixture = $pathHere . '/_files/valid_file_to_upload.cer';
        $_FILES = [
            $fileId => [
                'name' => 'valid_file_to_upload.cer',
                'type' => 'text/plain',
                'tmp_name' => $pathForFixture,
                'error' => 0,
                'size' => 1
            ]
        ];
        $validator->expects($this->any())
            ->method('isValid')
            ->willReturn(true);
        $uploader = new Uploader($fileId, $coreFileStorageDb, $coreFileStorage, $validator, $configWriter, $filesystem);

        $uploader->setCode('code');
        $uploader->setScope('scope');
        $uploader->setScopeId('scopeId');

        $configWriter->expects($this->any())
            ->method('save')
            ->with('payment/code/certificate', $this->anything(), 'scope', 'scopeId');

        $result = $uploader->saveCertificate();
        $this->assertNotEmpty($result);
    }

    public function testUploaderDoesNotSaveCertificateWhenValidationFails()
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $writeFactoryMock = $this->createMock(WriteFactory::class);
        $writeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->createMock(Write::class));

        $filesystem = $this->objectManagerHelper->getObject(
            Filesystem::class,
            ['writeFactory' => $writeFactoryMock]
        );

        $imageMock = $this->createMock(Image::class);
        $imageMock->expects($this->any())
            ->method('isValid')
            ->willReturn(false);
        $directoryListMock = $this->createMock(DirectoryList::class);
        $targetDirectoryMock = $this->createMock(TargetDirectory::class);
        $mimeMock = $this->createMock(Mime::class);
        $driverPoolMock = $this->createMock(DriverPool::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->withConsecutive(
                [Filesystem::class],
                [DirectoryList::class],
                [TargetDirectory::class],
                [Filesystem::class],
                [Mime::class],
                [DriverPool::class],
                [Image::class]
            )
            ->willReturnOnConsecutiveCalls(
                $filesystem,
                $directoryListMock,
                $targetDirectoryMock,
                $filesystem,
                $mimeMock,
                $driverPoolMock,
                $imageMock
            );
        ObjectManager::setInstance($objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $fileId = 'fileId';
        $coreFileStorageDb = $this->createMock(Database::class);
        $coreFileStorage = $this->createMock(Storage::class);
        $validator = $this->createMock(NotProtectedExtension::class);
        $configWriter = $this->createMock(WriterInterface::class);
        $pathHere = realpath(__DIR__);
        $pathForFixture = $pathHere . '/_files/invalid_file_to_upload.cer';
        $_FILES = [
            $fileId => [
                'name' => 'invalid_file_to_upload.txt',
                'type' => 'text/plain',
                'tmp_name' => $pathForFixture,
                'error' => 0,
                'size' => 1
            ]
        ];
        $validator->expects($this->any())
            ->method('isValid')
            ->willReturn(true);
        $uploader = new Uploader($fileId, $coreFileStorageDb, $coreFileStorage, $validator, $configWriter, $filesystem);

        $uploader->setCode('code');
        $uploader->setScope('scope');
        $uploader->setScopeId('scopeId');

        $configWriter->expects($this->any())
            ->method('save')
            ->with('payment/code/certificate', $this->anything(), 'scope', 'scopeId');

        $result = $uploader->saveCertificate();

        $this->assertNull($result);
    }

    public function testUploaderDoesNotSaveCertificateWhenConstructorFails()
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $writeFactoryMock = $this->createMock(WriteFactory::class);
        $writeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->createMock(Write::class));

        $filesystem = $this->objectManagerHelper->getObject(
            Filesystem::class,
            ['writeFactory' => $writeFactoryMock]
        );

        $imageMock = $this->createMock(Image::class);
        $imageMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);
        $directoryListMock = $this->createMock(DirectoryList::class);
        $targetDirectoryMock = $this->createMock(TargetDirectory::class);
        $mimeMock = $this->createMock(Mime::class);
        $driverPoolMock = $this->createMock(DriverPool::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->withConsecutive(
                [Filesystem::class],
                [DirectoryList::class],
                [TargetDirectory::class],
                [Filesystem::class],
                [Mime::class],
                [DriverPool::class],
                [Image::class]
            )
            ->willReturnOnConsecutiveCalls(
                $filesystem,
                $directoryListMock,
                $targetDirectoryMock,
                $filesystem,
                $mimeMock,
                $driverPoolMock,
                $imageMock
            );
        ObjectManager::setInstance($objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $fileId = 'fileId';
        $coreFileStorageDb = $this->createMock(Database::class);
        $coreFileStorage = $this->createMock(Storage::class);
        $validator = $this->createMock(NotProtectedExtension::class);
        $configWriter = $this->createMock(WriterInterface::class);
        $pathHere = realpath(__DIR__);
        $pathForFixture = $pathHere . '/_files/invalid_file_to_upload.txt';
        $_FILES = [
            $fileId => [
                'name' => 'invalid_file_to_upload.txt',
                'type' => 'text/plain',
                'tmp_name' => '',
                'error' => 0,
                'size' => 1
            ]
        ];
        $validator->expects($this->any())
            ->method('isValid')
            ->willReturn(true);
        $this->expectException(DomainException::class);
        $uploader = new Uploader($fileId, $coreFileStorageDb, $coreFileStorage, $validator, $configWriter, $filesystem);

        $uploader->setCode('code');
        $uploader->setScope('scope');
        $uploader->setScopeId('scopeId');

        $configWriter->expects($this->any())
            ->method('save')
            ->with('payment/code/certificate', $this->anything(), 'scope', 'scopeId');

        $result = $uploader->saveCertificate();

    }

    protected function setUp(): void
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);

        ObjectManager::setInstance($objectManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }
}
