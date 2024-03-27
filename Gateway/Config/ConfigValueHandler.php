<?php

namespace Pointspay\Pointspay\Gateway\Config;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class ConfigValueHandler extends \Magento\Payment\Gateway\Config\ConfigValueHandler
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $configInterface = null;

    /**
     * @param \Magento\Payment\Gateway\ConfigInterface $configInterface
     */
    public function __construct(
        ConfigInterface $configInterface = null
    ) {
        $this->configInterface = $configInterface;
        parent::__construct($configInterface);
    }

    /**
     * @param $configInterface
     * @return void
     */
    public function setConfig($configInterface)
    {
        $this->configInterface = $configInterface;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null)
    {
        if (isset($subject['pp_code'])){
            $this->configInterface->setMethodCode($subject['pp_code']);
        }
        return $this->configInterface->getValue(SubjectReader::readField($subject), $storeId);
    }

}
