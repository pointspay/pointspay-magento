<?php

namespace Pointspay\Pointspay\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Store\Model\ScopeInterface;
use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;
use Pointspay\Pointspay\Service\PaymentsReader;
use Psr\Log\LoggerInterface;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Pointspay\Pointspay\Service\PaymentsReader
     */
    private $paymentsReader;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    private $componentRegistrar;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        PaymentsReader $paymentsReader,
        ComponentRegistrarInterface $componentRegistrar
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->paymentsReader = $paymentsReader;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isEnabledGeneralPayment($storeId = null)
    {
        return $this->isEnabledPayment(PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId);
    }
    /**
     * @param $subPaymentCode
     * @param $storeId
     * @return mixed
     */
    public function isEnabledPayment($subPaymentCode, $storeId = null)
    {
        if (strpos($subPaymentCode, '_required_settings') === false) {
            $subPaymentCode .= '_required_settings';
        }
        $path = "payment/{$subPaymentCode}/active";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $subPaymentCode
     * @param $storeId
     * @return mixed|string
     */
    public function getSubPaymentTitle($subPaymentCode, $storeId = null)
    {
        if (strpos($subPaymentCode, '_required_settings') === false) {
            $subPaymentCode .= '_required_settings';
        }
        $path = "payment/{$subPaymentCode}/title";
        // could be overwriten by the merchant
        $dbResult = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        if ($dbResult) {
            return $dbResult;
        } else {
            $xmlResult = $subPaymentCode;
            foreach ($this->paymentsReader->getAvailablePointspayMethods() as $key => $method) {
                if ($method['pointspay_code'] == $subPaymentCode) {
                    $xmlResult = $method['name'];
                    break;
                }
                $subPaymentCode = str_replace('_required_settings', '', $subPaymentCode);
                if ($method['pointspay_code'] == $subPaymentCode) {
                    $xmlResult = $method['name'];
                    break;
                }
            }
            return $xmlResult;
        }
    }

    /**
     * @param $subPaymentCode
     * @param $storeId
     * @return mixed
     */
    public function getGeneralPaymentTitle($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
    {
        $path = "payment/{$subPaymentCode}/title";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $subPaymentCode
     * @param $storeId
     * @return mixed
     */
    public function getPaymentMode($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
    {
        if (empty($subPaymentCode)) {
            $subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS;
        }
        $originalSubPaymentCode = $subPaymentCode;
        if (strpos($subPaymentCode, '_required_settings') === false) {
            $subPaymentCode .= '_required_settings';
        }
        $path = "payment/{$subPaymentCode}/demo_mode";
        $storeScopeValue = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        if (empty($storeScopeValue)) {
            $path = "payment/{$originalSubPaymentCode}/demo_mode";
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $storeScopeValue;
        }
    }

    /**
     * @param $subPaymentCode
     * @param $storeId
     * @return mixed
     */
    public function getDebugMode($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
    {
        $originalSubPaymentCode = $subPaymentCode;
        if (strpos($subPaymentCode, '_required_settings') === false) {
            $subPaymentCode .= '_required_settings';
        }
        $path = "payment/{$subPaymentCode}/debug";
        $storeScopeValue = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        if (empty($storeScopeValue)) {
            $path = "payment/{$originalSubPaymentCode}/debug";
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $storeScopeValue;
        }
    }

    /**
     * @param $subPaymentCode
     * @param $storeId
     * @return mixed
     */
    public function getShopCode($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
    {
        $subPaymentCode .= '_required_settings';
        $path = "payment/{$subPaymentCode}/shop_code";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $subPaymentCode
     * @param $storeId
     * @return mixed
     */
    public function getConsumerKey($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
    {
        $subPaymentCode .= '_access_settings';
        $path = "payment/{$subPaymentCode}/consumer_key";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $subPaymentCode
     * @param $storeId
     * @return mixed
     */
    public function getPointspayCertificate($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
    {
        $subPaymentCode .= '_access_settings';
        $path = "payment/{$subPaymentCode}/certificate";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return \Pointspay\Pointspay\Service\PaymentsReader
     */
    public function getPaymentsReader()
    {
        return $this->paymentsReader;
    }

    /**
     * @return mixed
     */
    public function getRequestTimeout()
    {
        return $this->scopeConfig->getValue('payment/pointspay_group_all_in_one/request_timeout');
    }

    /**
     * Get custom cancel_url
     * @return mixed
     */
    public function getCancelUrl($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
    {
        if (strpos($subPaymentCode, '_required_settings') === false) {
            $subPaymentCode .= '_required_settings';
        }
        return $this->scopeConfig->getValue("payment/{$subPaymentCode}/cancel_url", ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Assuming that the main switch in the payment/pointspay_required_settings/demo_mode is the same for requesting payment methods
     * 0 - Live
     * 1 - Sandbox
     * @return string
     */
    public function getApiEndpoint($code = null)
    {
        if ($this->getPaymentMode($code) == '0') {
            $url = \Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface::POINTSPAY_LIVE_URL;
        } else {
            $url = \Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface::POINTSPAY_SANDBOX_URL;
        }
        return $url;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue('payment/pointspay_group_all_in_one/api_key');
    }

    /**
     * Get Pointspay magento module's version from composer.json
     *
     * @return string
     */
    public function getModuleVersion()
    {
        $moduleDir = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            'Pointspay_Pointspay'
        );

        $composerJson = file_get_contents($moduleDir . '/composer.json');
        $composerJson = json_decode($composerJson, true);

        if (empty($composerJson['version'])) {
            return "Version is not available in composer.json";
        }

        return $composerJson['version'];
    }

}
