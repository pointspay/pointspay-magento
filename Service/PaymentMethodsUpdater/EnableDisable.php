<?php

namespace Pointspay\Pointspay\Service\PaymentMethodsUpdater;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Pointspay\Pointspay\Api\Data\PaymentMethodsUpdaterInterface;
use Pointspay\Pointspay\Service\PaymentsReader;

class EnableDisable implements PaymentMethodsUpdaterInterface
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
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
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
        $select = $connection->select();
        $select->from($connection->getTableName('core_config_data'));
        $select->where('path LIKE \'%_required_settings/active\'');
        $currentData = $connection->fetchAll($select);
        foreach ($currentData as $currentKey => $currentDatum) {
            $intermediateScopeString = explode('_required_settings/active', $currentDatum['path']);
            $explodedBySlash = explode('/', reset($intermediateScopeString));
            $virtualMethodCode = end($explodedBySlash);
            $currentData[$virtualMethodCode] = $currentDatum;
            unset($currentData[$currentKey]);
        }
        $availableMethods =[];
        foreach ($this->paymentsReader->getAvailablePointspayMethods() as $methodNameFromXml => $methodFromXml) {
            if (
                (isset($methodFromXml['live']['enabled']) && $methodFromXml['live']['enabled'] == false)
                && (isset($methodFromXml['sandbox']['enabled']) && $methodFromXml['sandbox']['enabled'] == false)) {
                // Do not include payment method if API says it is disabled in both live and sandbox
                continue;
            } else {
                $availableMethods[$methodNameFromXml] = $methodFromXml;
            }
        }
        $disabledMethodsByApi = array_diff(array_keys($this->paymentsReader->getAvailablePointspayMethods()), array_keys($availableMethods));
        $keysFromXml = array_keys($availableMethods);
        $keysFromDB = array_keys($currentData);
        $processedKeysFromDB = $keysFromDB;
        foreach ($keysFromDB as $keyFromDB) {
            foreach ($keysFromXml as $keyFromXml) {
                if ($keyFromXml == $keyFromDB) {
                    //processed (exists in both)
                    unset($processedKeysFromDB[array_search($keyFromDB, $processedKeysFromDB)]);
                }
            }
        }
        if (count($processedKeysFromDB) > 0) {
            foreach ($processedKeysFromDB as $processedKeyFromDB) {
                $methodCode = $processedKeyFromDB;
                if (strpos($methodCode, '_required_settings') === false) {
                    $methodCode = $methodCode . '_required_settings';
                }
                $this->configWriter->save('payment/' . $methodCode . '/active', '0');
                foreach ($this->storeManager->getWebsites(true) as $website) {
                    $this->configWriter->delete('payment/' . $methodCode . '/active', 'websites', $website->getId());
                }
            }
        }
        foreach ($disabledMethodsByApi as $disabledMethodByApi) {
            $methodCode = $disabledMethodByApi;
            if (strpos($methodCode, '_required_settings') === false) {
                $methodCode = $methodCode . '_required_settings';
            }
            $this->configWriter->save('payment/' . $methodCode . '/active', '0');
            foreach ($this->storeManager->getWebsites(true) as $website) {
                $this->configWriter->delete('payment/' . $methodCode . '/active', 'websites', $website->getId());
            }
        }
    }
}
