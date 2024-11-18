<?php

namespace Pointspay\Pointspay\Service\PaymentMethodsUpdater;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Api\Data\PaymentMethodsUpdaterInterface;
use Pointspay\Pointspay\Service\PaymentsReader;

class Country implements PaymentMethodsUpdaterInterface
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Pointspay\Pointspay\Service\PaymentsReader
     */
    private $paymentsReader;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Pointspay\Pointspay\Service\PaymentsReader $paymentsReader
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        PaymentsReader $paymentsReader,
        ResourceConnection $resourceConnection
    ) {
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->paymentsReader = $paymentsReader;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $connection = $this->resourceConnection->getConnection();
        //  $connection->beginTransaction();
        $select = $connection->select();
        $select->from($connection->getTableName('core_config_data'));
        $select->where('path LIKE \'%_required_settings/specificcountry\'');
        $currentData = $connection->fetchAll($select);
        $currentDataByLevel = [];// = array_column($currentData, 'value', 'path');
        foreach ($currentData as $data) {
            $currentDataByLevel[$data['scope']][$data['scope_id']][$data['path']] = $data['value'];
        }
        foreach ($this->paymentsReader->getAvailablePointspayMethods() as $method) {
            isset($method['applicableCountries']) ? $countries = $method['applicableCountries'] : $countries = [];
            $countriesList = [];
            array_walk($countries, function (&$value) use (&$countriesList) {
                $countriesList[] = $value['code'];
            });
            $methodCode = $method['pointspay_code'];
            if (strpos($methodCode, '_required_settings') === false) {
                $methodCode = $methodCode . '_required_settings';
            }
            $this->configWriter->save('payment/' . $methodCode . '/allowspecific', 1);
            $this->configWriter->save('payment/' . $methodCode . '/specificcountry', implode(',', $countriesList));
            foreach ($this->storeManager->getWebsites(true) as $website) {
                $this->configWriter->delete('payment/' . $methodCode . '/allowspecific', 'websites', $website->getId());
                $this->configWriter->delete('payment/' . $methodCode . '/specificcountry', 'websites', $website->getId());
            }
        }
        foreach ($currentDataByLevel as $scopeCode => $scopes) {
            foreach ($scopes as $scopeId => $data) {
                if ($scopeId == 0 && $scopeCode == 'websites') {
                    continue;
                }
                foreach ($data as $path => $value) {
                    $this->configWriter->save($path, $value, $scopeCode, $scopeId);
                }
            }
        }
    }
}
