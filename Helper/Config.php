<?php

namespace Pointspay\Pointspay\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
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


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        PaymentsReader $paymentsReader

    ) {
        $this->scopeConfig = $scopeConfig;
        $this->paymentsReader = $paymentsReader;
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
        if (strpos($subPaymentCode, '_required_settings') === false) {
            $subPaymentCode .= '_required_settings';
        }
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
        $subPaymentCode = str_replace('_required_settings', '', $subPaymentCode);
        if (strpos($subPaymentCode, '_access_settings') === false) {
            $subPaymentCode .= '_access_settings';
        }
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
        $subPaymentCode = str_replace('_required_settings', '', $subPaymentCode);
        if (strpos($subPaymentCode, '_access_settings') === false) {
            $subPaymentCode .= '_access_settings';
        }
        $path = "payment/{$subPaymentCode}/certificate";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return array
     */
    public function getEnabledPaymentMethodsDetails()
    {
        $payments = [];
        foreach ($this->paymentsReader->getAvailablePointspayMethods() as $key => $method) {
            if (!$this->isEnabledPayment($method['pointspay_code'])) {
                continue;
            }
            $method['title'] = $this->getSubPaymentTitle($method['pointspay_code']) ?? $method['name'];
            $method['code'] = $method['pointspay_code'] . '_required_settings';
            $method['isActive'] = true;
            $languageCode = $this->scopeConfig->getValue('general/locale/code');
            $langISO = explode('_', $languageCode ?: 'en_US');
            $langCode= reset($langISO);
            $logoParams = [
                'shop_code' => $this->getShopCode($method['pointspay_code']),
                'language'=> $langCode ?: 'en'
            ];
            $paymentModeBool = $this->getPaymentMode($method['pointspay_code']);
            // zero for Live, one for Sandbox
            $paymentModeSelector = 'live';
            if ($paymentModeBool == 1) {
                $paymentModeSelector = 'sandbox';
            }
            $logoUrl =isset($method[$paymentModeSelector]['logo']) ? sprintf('%s', $method[$paymentModeSelector]['logo']) : 'https://secure.pointspay.com/checkout/user/btn-img-v2';
            $method['logo'] = sprintf(
                '%s?%s',
                $logoUrl,
                http_build_query($logoParams)
            );
            $payments[] = $method;
        }
        return $payments;
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

}
