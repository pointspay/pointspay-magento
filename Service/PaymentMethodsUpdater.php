<?php

namespace Pointspay\Pointspay\Service;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use Pointspay\Pointspay\Api\Data\ApiInterface;
use Pointspay\Pointspay\Service\PaymentMethodsUpdater\ExecutionChain;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Zend_Cache;
use Magento\Framework\App\Config\Storage\WriterInterface;

class PaymentMethodsUpdater
{
    const URL_SUFFIX_FOR_LOGO = 'checkout/user/btn-img-v2';

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    private $configCacheType;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $filesystemIo;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Pointspay\Pointspay\Service\PaymentMethodsUpdater\ExecutionChain
     */
    private $executionChainDataModifier;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem\Io\File $filesystemIo
     * @param \Pointspay\Pointspay\Api\Data\ApiInterface $api
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Pointspay\Pointspay\Service\PaymentMethodsUpdater\ExecutionChain $executionChainDataModifier
     */
    public function __construct(
        Config              $configCacheType,
        Reader              $moduleReader,
        File                $filesystemIo,
        ApiInterface        $api,
        SerializerInterface $serializer,
        ExecutionChain      $executionChainDataModifier,
        LoggerInterface     $logger,
        Filesystem          $filesystem,
        WriterInterface     $configWriter
    )
    {
        $this->configCacheType = $configCacheType;
        $this->moduleReader = $moduleReader;
        $this->filesystemIo = $filesystemIo;
        $this->api = $api;
        $this->serializer = $serializer;
        $this->executionChainDataModifier = $executionChainDataModifier;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->configWriter = $configWriter;
    }

    /**
     * Execute updating of the intermediate file with payment methods and then copy it to the main file
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        $contentFromApi = $this->api->getPaymentMethods();

        if($contentFromApi === null) {
            $this->logger->warning('Error getting payment methods data from API');
            return;
        }

        $this->logger->addInfo('Content from API', $contentFromApi);
        $filteredContentFromApi = $this->filterContent($contentFromApi);
        $this->logger->addInfo('Filtered content from API', $filteredContentFromApi);

        // save JSON encoded data to database
        $jsonEncodedData = $this->serializer->serialize($filteredContentFromApi);
        $this->logger->addInfo('JSON Result', ['content' => $jsonEncodedData]);
        $this->configWriter->save('payment/pointspay_available_methods_list', $jsonEncodedData);
        $this->configCacheType->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['payment_config', 'pointspay_payment_config', 'config']);
        $this->configCacheType->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['payment_config', 'pointspay_payment_config', 'config']);

        $this->executionChainDataModifier->execute();
    }

    /**
     * Filter content from API
     * point of extension of filtering is to be able to filter out some payment methods
     *
     * @param array $contentFromApi
     * @return array
     */
    public function filterContent($contentFromApi)
    {
        $result = [];
        if (is_array($contentFromApi)) {
            foreach ($contentFromApi as $key => &$value) {
                if (isset($value['code']) && strtoupper($value['code']) == 'PP') {
                    $value['code'] = 'pointspay';
                }

                $value['code'] = strtolower($value['code']);

                if (
                    (isset($value['live']['enabled']) && $value['live']['enabled'] == false)
                    && (isset($value['sandbox']['enabled']) && $value['sandbox']['enabled'] == false)) {
                    // Do not include payment method if API says it is disabled in both live and sandbox
                    continue;
                } else {
                    if (isset($value['sandbox']['baseDomain'])) {
                        $baseDomain = $value['sandbox']['baseDomain'];
                        $baseDomain = rtrim($baseDomain, '/');
                        $value['sandbox']['logo'] = sprintf('%s/%s', $baseDomain, self::URL_SUFFIX_FOR_LOGO);
                    }
                    if (isset($value['live']['baseDomain'])) {
                        $baseDomain = $value['live']['baseDomain'];
                        $baseDomain = rtrim($baseDomain, '/');
                        $value['live']['logo'] = sprintf('%s/%s', $baseDomain, self::URL_SUFFIX_FOR_LOGO);
                    }
                    $result[$key] = $value;
                }
            }
            return $result;
        } else {
            return $contentFromApi;
        }
    }
}
