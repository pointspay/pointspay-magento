<?php

namespace Pointspay\Pointspay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Option\ArrayInterface;
use Pointspay\Pointspay\Helper\Config;

class DemoMode extends DataObject implements OptionSourceInterface, ArrayInterface
{
    private $path ='';

    /**
     * @var mixed
     */
    private $optionsByPointspay=[];

    /**
     * @var \Pointspay\Pointspay\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Pointspay\Pointspay\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        Config $configHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->configHelper = $configHelper;
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->getPath()) {
            $options = $this->returnAllEnv();
        } else {
            $intermediateScopeString = explode('_required_settings/demo_mode', $this->getPath());
            $explodedBySlash = explode('/', reset($intermediateScopeString));
            $virtualMethodCode = end($explodedBySlash);
            $availableMethods = $this->configHelper->getPaymentsReader()->getAvailablePointspayMethods();
            if (!in_array($virtualMethodCode, array_keys($availableMethods))) {
                $options = $this->returnAllEnv();
            } else {
                $processedOptions = [];
                if (isset($availableMethods[$virtualMethodCode]['live']['enabled']) && $availableMethods[$virtualMethodCode]['live']['enabled'] == 1) {
                    $processedOptions[] =  ['value' => '0', 'label' => 'Live'];
                }
                if (isset($availableMethods[$virtualMethodCode]['sandbox']['enabled']) && $availableMethods[$virtualMethodCode]['sandbox']['enabled'] == 1) {
                    $processedOptions[] =   ['value' => '1', 'label' => 'Sandbox'];
                }
                $this->optionsByPointspay[$virtualMethodCode] = $processedOptions;
                $options = $this->optionsByPointspay[$virtualMethodCode];
            }
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
     * @return array[]
     */
    protected function returnAllEnv(): array
    {
        return [
            ['value' => '0', 'label' => 'Live'],
            ['value' => '1', 'label' => 'Sandbox']
        ];
    }

}
