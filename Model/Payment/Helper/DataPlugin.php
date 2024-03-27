<?php

namespace Pointspay\Pointspay\Model\Payment\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Factory;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\ScopeInterface;
use UnexpectedValueException;

class DataPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Payment\Model\Method\Factory
     */
    private $paymentMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Factory $paymentMethodFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->paymentMethodFactory = $paymentMethodFactory;
    }
    /**
     * @param Data $subject
     * @param array $result
     * @return array
     */
    public function afterGetPaymentMethods(Data $subject, array $result): array
    {
        foreach ($result as $code => $data) {
            if (strpos($code, '_access_settings') !== false) {
                unset($result[$code]);
            }
            if (strpos($code, 'pointspay_group_all_in_one') !== false) {
                unset($result[$code]);
            }
        }
        return $result;
    }

    /**
     * Retrieve method model object
     *
     * @param string $code
     *
     * @return MethodInterface
     * @throws LocalizedException
     */
    public function aroundGetMethodInstance(Data $subject, callable $proceed, $code)
    {
        if (strpos($code, '_required_settings') === false) {
            return $proceed($code);
        }
        return $this->getPaymentMethodInstance($code);
    }

    // payment/<code>/model
    private function getPaymentMethodInstance(string $code)
    {
        $class = $this->scopeConfig->getValue(
            $this->getMethodModelConfigName($code),
            ScopeInterface::SCOPE_STORE
        );

        if (!$class) {
            throw new UnexpectedValueException('Payment model name is not provided in config!');
        }

        return $this->paymentMethodFactory->create($class, ['code' => $code]);
    }
    /**
     * Get config name of method model
     *
     * @param string $code
     * @return string
     */
    protected function getMethodModelConfigName($code)
    {
        return sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, $code);
    }
}
