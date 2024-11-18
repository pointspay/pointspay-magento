<?php

namespace Pointspay\Pointspay\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Pointspay\Pointspay\Service\PaymentsReader;

class AllowedCountries extends Value
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Pointspay\Pointspay\Service\PaymentsReader
     */
    private $paymentsReader;

    /**
     * Logger instance for error tracking.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Serializer for encoding and decoding data.
     *
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    public function __construct(
        Context                                          $context,
        Registry                                         $registry,
        ScopeConfigInterface                             $config,
        TypeListInterface                                $cacheTypeList,
        WriterInterface                                  $configWriter,
        PaymentsReader                                   $paymentsReader,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        AbstractResource                                 $resource = null,
        AbstractDb                                       $resourceCollection = null,
        array                                            $data = []
    )
    {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->configWriter = $configWriter;
        $this->paymentsReader = $paymentsReader;
        $this->serializer = $serializer;
    }

    public function afterSave()
    {
        $result = parent::afterSave();
        if ($this->getValue() == '0') {
            $this->_getResource()->addCommitCallback([$this, 'processAfterSave']);
        }
        return $result;
    }

    public function processAfterSave()
    {
        $methodList = $this->paymentsReader->getAvailablePointspayMethods();

        if (empty($methodList)) {
            return null;
        }

        $configPath = $this->getPath();
        $paymentMethodCode = explode('_', explode('/', $configPath)[1])[0];

        $method = $this->findPaymentMethodByCode($methodList, $paymentMethodCode);

        if (empty($method)) {
            return null;
        }

        $countriesList = $this->getApplicableCountriesList($method);

        $this->configWriter->save(
            sprintf('payment/%s_required_settings/specificcountry', $paymentMethodCode),
            implode(',', $countriesList),
            $this->getScope(),
            $this->getScopeId()
        );
    }

    /**
     * Finds a specific payment method from the list by its code.
     *
     * @param array $methodList List of available payment methods.
     * @param string $paymentMethodCode The code of the payment method to find.
     * @return array|null Returns the payment method array if found, or null if not found.
     */
    private function findPaymentMethodByCode(array $methodList, string $paymentMethodCode)
    {
        foreach ($methodList as $availableMethod) {
            if (isset($availableMethod['code']) && $availableMethod['code'] === $paymentMethodCode) {
                return $availableMethod;
            }
        }
        return null;
    }

    /**
     * Retrieves a list of country codes where the payment method is applicable.
     *
     * @param array $method The payment method array which contains applicable countries.
     * @return array List of country codes where the payment method is available.
     */
    private function getApplicableCountriesList(array $method)
    {
        $countriesList = [];
        if (!empty($method['applicableCountries']) && is_array($method['applicableCountries'])) {
            foreach ($method['applicableCountries'] as $country) {
                $countriesList[] = $country['code'];
            }
        }
        return $countriesList;
    }
}
