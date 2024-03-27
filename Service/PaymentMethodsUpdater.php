<?php

namespace Pointspay\Pointspay\Service;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Exception\LocalizedException;
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
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem\Io\File $filesystemIo
     * @param \Pointspay\Pointspay\Api\Data\ApiInterface $api
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Pointspay\Pointspay\Service\PaymentMethodsUpdater\ExecutionChain $executionChainDataModifier
     */
    public function __construct(
        Config $configCacheType,
        Reader $moduleReader,
        File $filesystemIo,
        ApiInterface $api,
        SerializerInterface $serializer,
        ExecutionChain $executionChainDataModifier,
        LoggerInterface $logger
    ) {
        $this->configCacheType = $configCacheType;
        $this->moduleReader = $moduleReader;
        $this->filesystemIo = $filesystemIo;
        $this->api = $api;
        $this->serializer = $serializer;
        $this->executionChainDataModifier = $executionChainDataModifier;
        $this->logger = $logger;
    }

    /**
     * Execute updating of the intermediate file with payment methods and then copy it to the main file
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $etcDir = $this->moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Pointspay_Pointspay');
        $availableMethodsFile = $etcDir . '/pointspay_methods_available.xml';
        $contentFromApi = $this->api->getPaymentMethods();
        $this->logger->addInfo('Content from API', $contentFromApi);
        $filteredContentFromApi = $this->filterContent($contentFromApi);
        $this->logger->addInfo('Filtered content from API', $filteredContentFromApi);
        $xmlContents = $this->createXmlByData($filteredContentFromApi);
        $content = $xmlContents->asXML();
        $this->logger->addInfo('XML Result', ['content' => $content]);
        $this->filesystemIo->write($availableMethodsFile, $content);
        $this->filesystemIo->rm($etcDir . '/pointspay_methods.xml');
        $this->filesystemIo->cp($availableMethodsFile, $etcDir . '/pointspay_methods.xml');
        $this->filesystemIo->rm($availableMethodsFile);
        $this->configCacheType->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['payment_config', 'pointspay_payment_config', 'config']);
        // no need to add the data inside chain because you MUST use interfaces like \Magento\Framework\App\Config\Storage\WriterInterface
        // please DON'T use \Pointspay\Pointspay\Model\Framework\App\Config\Initital\ConverterPlugin::afterConvert to save the data
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

    /**
     * @param array $data
     * @return \SimpleXMLElement
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createXmlByData($data)
    {
        // in this way because with addChild() method it is not possible to add attributes in a correct way for example
        // xsi:noNamespaceSchemaLocation without xsi prefix
        // xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" without xmlns prefix
        $xmlStr = <<<XML
<?xml version='1.0'?>
<payment xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="urn:magento:module:Pointspay_Pointspay:etc/pointspay_methods.xsd">
<pointspay_methods></pointspay_methods>
</payment>
XML;
        $xml = new SimpleXMLElement($xmlStr);
        foreach ($data as $key => $value) {
            $type = $xml->pointspay_methods->addChild('type');
            $type->addAttribute('id', strtolower($value['code']));
            $type->addAttribute('order', $key);
            $type->addChild('label', $value['name']);
            $type->addChild('pointspay_code', strtolower($value['code']));
            $sandbox = $type->addChild('sandbox');
            $this->assocToXml($value['sandbox'], 'sandbox', $sandbox);
            $live = $type->addChild('live');
            $this->assocToXml($value['live'], 'live', $live);
            $applicableCountries = $type->addChild('applicableCountries');
            foreach ($value['applicableCountries'] as $countryKey => $countryValue) {
                $country = $applicableCountries->addChild('country');
                $country->addChild('code', $countryValue['code']);
                $country->addChild('name', $countryValue['name']);
            }
        }
        return $xml;
    }

    /**
     * Function, that actually recursively transforms array to xml
     *
     * @param array $array
     * @param string $rootName
     * @param \SimpleXMLElement $xml
     * @return \SimpleXMLElement
     * @throws LocalizedException
     */
    private function assocToXml($array, $rootName, SimpleXMLElement $xml)
    {
        $hasNumericKey = false;
        $hasStringKey = false;
        if (!is_array($array)) {
            $array = (array)$array;
        }
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                if (is_string($key)) {
                    if ($key === $rootName) {
                        throw new LocalizedException(
                            new Phrase(
                                "An associative key can't be the same as its parent associative key. "
                                . "Verify and try again."
                            )
                        );
                    }
                    $hasStringKey = true;
                    if (is_bool($value)) {
                        // Convert boolean to string boolean due to XML limitations of boolean values
                        $value = $value ? 1 : 0;
                    }
                    $xml->addChild($key, $value);
                } elseif (is_int($key)) {
                    $hasNumericKey = true;
                    $xml->addChild($key, $value);
                }
            } else {
                $xml->addChild($key);
                self::assocToXml($value, $key, $xml->{$key});
            }
        }
        if ($hasNumericKey && $hasStringKey) {
            throw new LocalizedException(
                new Phrase(
                    "Associative and numeric keys can't be mixed at one level. Verify and try again."
                )
            );
        }
        return $xml;
    }
}
