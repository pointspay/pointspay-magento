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

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        WriterInterface $configWriter,
        PaymentsReader $paymentsReader,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->configWriter = $configWriter;
        $this->paymentsReader = $paymentsReader;
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
        $paymentCode = str_replace('_required_settings', '', $this->getGroupId());
        $methodList = $this->paymentsReader->getAvailablePointspayMethods();
        if (empty($methodList)) {
            return null;
        }
        $method = isset($methodList[$paymentCode]) ? $methodList[$paymentCode] : null;
        if (empty($method)) {
            return null;
        }
        isset($method['applicableCountries']) ? $countries = $method['applicableCountries'] : $countries = [];
        $countriesList = [];
        array_walk($countries, function (&$value) use (&$countriesList) {
            $countriesList[] = $value['code'];
        });
        $this->configWriter->save(sprintf('payment/%s/specificcountry', $this->getGroupId()), implode(',', $countriesList), $this->getScope(), $this->getScopeId());
    }
}