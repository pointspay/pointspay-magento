<?php

namespace Pointspay\Pointspay\Gateway\Validator;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Pointspay\Pointspay\Model\Config\Source\Country;

class CountryValidator extends AbstractValidator
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $config = null;

    /**
     * @var \Pointspay\Pointspay\Model\Config\Source\Country
     */
    private $countrySource;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Country $countrySource,
        WriterInterface $configWriter,
        ConfigInterface $config = null
    ) {
        parent::__construct($resultFactory);
        $this->config = $config;
        $this->countrySource = $countrySource;
        $this->configWriter = $configWriter;
    }

    public function setConfig($configInterface)
    {
        $this->config = $configInterface;
    }
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $storeId = $validationSubject['storeId'];
        $this->config->setMethodCode($validationSubject['pp_code']);
        $availableCountries = explode(
            ',',
            $this->config->getValue('specificcountry', $storeId) ?? ''
        ) ?: [];
        /**
         * Need to validate if the country is allowed by API. not regular validation as it is done by Magento
         * @see \Magento\Payment\Gateway\Validator\CountryValidator::validate
         * if we bypass this, the API will NOT return an error but requirements are not met.
         * no need to use allowspecific as it is not used by the API
         */
        $availableCountries = array_filter($availableCountries);
        if (empty($availableCountries)) {
            $this->countrySource->setPath('payment/' . $validationSubject['pp_code'] . '/specificcountry');
            $availableCountriesByOptionArray = $this->countrySource->toOptionArray(true);
            foreach ($availableCountriesByOptionArray as $country) {
                if (!empty($country['value'])) {
                    $availableCountries[] = $country['value'];
                }
            }
            //set the available countries to the config if not set anywhere
            if ((int)$this->config->getValue('allowspecific', $storeId) === 0){
                $this->configWriter->save(
                    'payment/' . $validationSubject['pp_code'] . '/specificcountry',
                    implode(',', $availableCountries)
                );
            }
        }
        if (!in_array($validationSubject['country'], $availableCountries)) {
            $isValid = false;
        }

        return $this->createResult($isValid);
    }
}
