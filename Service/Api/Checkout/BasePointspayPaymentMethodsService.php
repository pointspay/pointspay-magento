<?php

namespace Pointspay\Pointspay\Service\Api\Checkout;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;
use Pointspay\Pointspay\Service\Api\CartProvider\CartProvider;

/**
 * Class BasePointspayPaymentMethodsService
 *
 * @package Pointspay\Pointspay\Service\Api\Checkout
 */
class BasePointspayPaymentMethodsService
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * BasePointspayPaymentMethodsService constructor.
     * @param Http $request
     * @param Json $jsonSerializer
     * @param CartProvider $cartProvider
     */
    public function __construct(
        Http                 $request,
        Json                 $jsonSerializer,
        CartProvider         $cartProvider,
        ScopeConfigInterface $scopeConfig,
        SerializerInterface  $serializer
    )
    {
        $this->request = $request;
        $this->jsonSerializer = $jsonSerializer;
        $this->cartProvider = $cartProvider;

        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * Retrieves the available payment methods for the specified cart, filters them by country and other conditions,
     * and sorts them by the sort order configuration.
     *
     * @param string $cartId The ID of the cart for which to retrieve available payment methods.
     * @return array An array of available payment methods with relevant details and sorted by sort order.
     */
    public function getAvailablePaymentMethods(string $cartId): array
    {
        $quote = $this->cartProvider->getQuote($cartId);
        $jsonData = $this->scopeConfig->getValue('payment/pointspay_available_methods_list');
        $availableMethods = $this->serializer->unserialize($jsonData ?? '[]');

        $payments = [];
        foreach ($availableMethods as $method) {
            if (!$this->isEnabledPayment($method['code'])) {
                continue;
            }

            $shippingCountry = $quote->getShippingAddress()->getCountryId();
            if (empty($shippingCountry) || !$this->isCountryAllowed($shippingCountry, $method['code'], $method['applicableCountries'])) {
                continue;
            }

            $method['title'] = $method['name'];
            $method['isActive'] = true;
            $method['sort_order'] = $this->getSortOrder($method['code']);

            $languageCode = $this->scopeConfig->getValue('general/locale/code');
            $langISO = explode('_', $languageCode ?: 'en_US');
            $langCode = reset($langISO);

            $logoParams = [
                'shop_code' => $this->getShopCode($method['code']),
                'language' => $langCode ?: 'en'
            ];

            $paymentModeBool = $this->getPaymentMode($method['code']);
            $paymentModeSelector = $paymentModeBool == 1 ? 'sandbox' : 'live';

            $logoUrl = isset($method[$paymentModeSelector]['logo'])
                ? sprintf('%s', $method[$paymentModeSelector]['logo'])
                : 'https://secure.pointspay.com/checkout/user/btn-img-v2';

            $method['logo'] = sprintf(
                '%s?%s',
                $logoUrl,
                http_build_query($logoParams)
            );

            unset($method['sandbox'], $method['live'], $method['applicableCountries']);
            $payments[] = $method;
        }

        usort($payments, function ($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });


        return $payments;
    }

    /**
     * Retrieves the sort order for a given payment method code from the configuration.
     * Defaults to 1 if the sort order is not set in the configuration.
     *
     * @param string $subPaymentCode The payment method code to retrieve the sort order for.
     * @param int|null $storeId Optional store ID for scope configuration.
     * @return int The sort order value from the configuration, or 1 if not set.
     */
    private function getSortOrder($subPaymentCode, $storeId = null): int
    {
        $subPaymentCode .= '_required_settings';
        $path = "payment/{$subPaymentCode}/sort_order";

        $sortOrder = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);

        return $sortOrder !== null ? (int)$sortOrder : 1;
    }

    /**
     * Checks if the given country is allowed based on configuration and available countries.
     *
     * @param string $countryId The country code to check.
     * @param string $subPaymentCode The payment method code.
     * @param array $allCountries List of all allowed countries.
     * @param int|null $storeId Optional store ID for scope configuration.
     * @return bool Returns true if the country is allowed, otherwise false.
     */
    private function isCountryAllowed($countryId, $subPaymentCode, $allCountries, $storeId = null)
    {
        $subPaymentCode .= '_required_settings';
        $allCountryCodes = array_map(function ($country) {
            return $country['code'];
        }, $allCountries);

        $allowSpecificFlag = $this->scopeConfig->isSetFlag(
            "payment/{$subPaymentCode}/allowspecific", ScopeInterface::SCOPE_STORE, $storeId
        );
        if (!$allowSpecificFlag) {
            return in_array($countryId, $allCountryCodes);
        }

        $allowedCountries = $this->scopeConfig->getValue(
            "payment/{$subPaymentCode}/specificcountry", ScopeInterface::SCOPE_STORE, $storeId
        );
        $allowedCountriesArray = explode(',', $allowedCountries);

        return in_array($countryId, $allowedCountriesArray) && in_array($countryId, $allCountryCodes);
    }


    /**
     * Checks if a specific payment method is enabled.
     *
     * @param string $subPaymentCode The code for the sub-payment method.
     * @param int|null $storeId The store ID for scope configuration.
     * @return mixed
     */
    private function isEnabledPayment($subPaymentCode, $storeId = null)
    {
        if (strpos($subPaymentCode, '_required_settings') === false) {
            $subPaymentCode .= '_required_settings';
        }
        $path = "payment/{$subPaymentCode}/active";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Retrieves the shop code for a specified sub-payment method.
     *
     * @param string $subPaymentCode The code for the sub-payment method.
     * @param int|null $storeId The store ID for scope configuration.
     * @return mixed
     */
    private function getShopCode($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
    {
        if (strpos($subPaymentCode, '_required_settings') === false) {
            $subPaymentCode .= '_required_settings';
        }
        $path = "payment/{$subPaymentCode}/shop_code";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Retrieves the payment mode (live or sandbox) for a specific sub-payment method.
     *
     * @param string $subPaymentCode The code for the sub-payment method.
     * @param int|null $storeId The store ID for scope configuration.
     * @return mixed
     */
    private function getPaymentMode($subPaymentCode = PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS, $storeId = null)
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
}
