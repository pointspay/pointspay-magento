<?php

namespace Pointspay\Pointspay\Model\Config\Source;

use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Pointspay\Pointspay\Helper\Config;

class Country extends DataObject implements OptionSourceInterface, ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_countryCollection;

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var array
     */
    private $optionsByPointspay = [];

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils
     */
    private $arrayUtils;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    private $localeLists;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @param \Pointspay\Pointspay\Helper\Config $configHelper
     * @param \Magento\Directory\Helper\Data $helperData
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        Collection $countryCollection,
        Config $configHelper,
        Data $helperData,
        ArrayUtils $arrayUtils,
        ResolverInterface $localeResolver,
        ListsInterface $localeLists,
        array $data = []
    ) {
        $this->_countryCollection = $countryCollection;
        $this->configHelper = $configHelper;
        $this->helperData = $helperData;
        $this->arrayUtils = $arrayUtils;
        $this->localeResolver = $localeResolver;
        parent::__construct($data);
        $this->localeLists = $localeLists;
    }

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray($isMultiselect = false, $foregroundCountries = '')
    {
        $options = [];
        if (!$this->getPath()) {
            // Process as a regular source model
            $options = $this->processLikeRegularSourceModel($foregroundCountries);
        } else {
            // Process as a custom source model with unsetting options (inapplicable countries by API)
            $intermediateScopeString = explode('_required_settings/specificcountry', $this->getPath());
            $explodedBySlash = explode('/', reset($intermediateScopeString));
            $virtualMethodCode = end($explodedBySlash);

            // Only proceed if options for this virtual method code are not cached
            if (!isset($this->optionsByPointspay[$virtualMethodCode])) {
                $availableMethods = $this->configHelper->getPaymentsReader()->getAvailablePointspayMethods();

                // Check if $availableMethods is an array and contains the $virtualMethodCode
                if (is_array($availableMethods) && in_array($virtualMethodCode, array_keys($availableMethods))) {
                    if (isset($availableMethods[$virtualMethodCode]['applicableCountries'])) {
                        $applicableCountries = $availableMethods[$virtualMethodCode]['applicableCountries'];
                        $processedOptions = $this->processLikePointspaySourceModel($foregroundCountries, $applicableCountries);
                        $this->optionsByPointspay[$virtualMethodCode] = $processedOptions;
                        $options = $this->optionsByPointspay[$virtualMethodCode];
                    } else {
                        // Fallback to processing like a regular source model
                        $options = $this->processLikeRegularSourceModel($foregroundCountries);
                    }
                } else {
                    // Fallback to processing like a regular source model if $availableMethods is invalid
                    $options = $this->processLikeRegularSourceModel($foregroundCountries);
                }
            } else {
                $options = $this->optionsByPointspay[$virtualMethodCode];
            }
        }

        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }
        return $options;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param $foregroundCountries
     * @return array
     */
    protected function processLikeRegularSourceModel($foregroundCountries)
    {
        if (!$this->_options) {
            $this->_options = $this->_countryCollection->loadData()->setForegroundCountries(
                $foregroundCountries
            )->toOptionArray(
                false
            );
        }
        return $this->_options;
    }

    /**
     * Original reference:
     *
     * @param $foregroundCountries
     * @param $applicableCountries
     * @return array
     * @see \Magento\Directory\Model\ResourceModel\Country\Collection::toOptionArray
     */
    private function processLikePointspaySourceModel($foregroundCountries, $applicableCountries)
    {
        $result = [];
        $foregroundCountries = (array)$foregroundCountries;

        $sort = $this->getSort($applicableCountries);

        $this->arrayUtils->ksortMultibyte($sort, $this->localeResolver->getLocale());
        foreach (array_reverse($foregroundCountries) as $foregroundCountry) {
            $name = array_search($foregroundCountry, $sort);
            if ($name) {
                unset($sort[$name]);
                $sort = [$name => $foregroundCountry] + $sort;
            }
        }

        $isRegionVisible = (bool)$this->helperData->isShowNonRequiredState();
        foreach ($sort as $countryName => $countryCode) {
            $option['value'] = $countryCode;
            $option['label'] = $countryName;
            if ($this->helperData->isRegionRequired($countryCode)) {
                $option['is_region_required'] = true;
            } else {
                $option['is_region_visible'] = $isRegionVisible;
            }
            if ($this->helperData->isZipCodeOptional($countryCode)) {
                $option['is_zipcode_optional'] = true;
            }
            $result[] = $option;
        }
        return $result;
    }

    /**
     * Get sort
     *
     * @param array $options
     * @return array
     */
    private function getSort(array $options): array
    {
        $sort = [];
        foreach ($options as $data) {
            $name = (string)$this->localeLists->getCountryTranslation($data['code']);
            if (!empty($name)) {
                $sort[$name] = $data['code'];
            }
        }

        return $sort;
    }
}
