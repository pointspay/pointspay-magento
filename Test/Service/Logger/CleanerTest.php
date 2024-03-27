<?php
namespace Pointspay\Pointspay\Test\Service\Logger;


use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Logger\Cleaner;
use Magento\Framework\Filesystem\Driver\File;

class CleanerTest extends TestCase
{
    private $filesystem;
    private $cleaner;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(File::class);
        $this->cleaner = new Cleaner($this->filesystem);
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testDirectoryIsCleanedWhenOlderThanThirtyDays()
    {
        $this->filesystem->method('readDirectory')->willReturn([
            '/var/log/pointspay/2022-01-01',
            '/var/log/pointspay/2022-02-01',
        ]);

        $this->filesystem->expects($this->any())->method('deleteDirectory')->with('/var/log/pointspay/2022-01-01');

        $this->cleaner->execute();
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testDirectoryIsNotCleanedWhenLessThanThirtyDaysOld()
    {
        $this->filesystem->method('readDirectory')->willReturn([
            '/var/log/pointspay/2022-02-01',
            '/var/log/pointspay/2022-03-01',
        ]);

        $this->filesystem->expects($this->never())->method('deleteDirectory');

        $this->cleaner->execute();
    }
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testNnDirectoryFilesAreIgnored()
    {
        $this->filesystem->method('readDirectory')->willReturn([
            '/var/log/pointspay/2022-01-01',
            '/var/log/pointspay/file.log',
        ]);

        $this->filesystem->method('isDirectory')->willReturnCallback(function ($path) {
            return $path !== '/var/log/pointspay/file.log';
        });

        $this->filesystem->expects($this->any())->method('deleteDirectory')->with('/var/log/pointspay/2022-01-01');

        $this->cleaner->execute();
    }
}
