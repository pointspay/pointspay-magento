<?php

namespace Pointspay\Pointspay\Test;

use Exception;
use Magento\Framework\App\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Framework\App\Http;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Autoload\ClassLoaderWrapper;

/**
 * Class MageObjMan
 * Real object manager
 */
class MageObjectManager
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * MageObjMan constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (!defined('BP')) {
            define('BP', dirname(__DIR__));
            $dir = substr(BP, 0, -60);
            //app/code/Pointspay\Pointspay\Test\MageObjectManager
        } else {
            $dir = BP;
        }
        if (!defined('VENDOR_PATH')) {
            define('VENDOR_PATH', $dir . '/app/etc/vendor_path.php');
        }
        $vendorDir = require VENDOR_PATH;
        $vendorAutoload = $dir . "/{$vendorDir}/autoload.php";
        /* 'composer install' validation */
        if (file_exists($vendorAutoload)) {
            $composerAutoloader = include $vendorAutoload;
        } else {
            throw new Exception(
                'Vendor autoload is not found. Please run \'composer install\' under application root directory.'
            );
        }
        AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));
        $bootstrap = Bootstrap::create($dir, $_SERVER);//->createApplication(Http::class);
        $this->objectManager = $bootstrap->getObjectManager();
    }
}
