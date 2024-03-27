<?php

namespace Pointspay\Pointspay\Model\Config\Structure\Data;

class DataChain
{

    /**
     * @var array
     */
    private $chain;

    /**
     * @param array $chainToModifyStructure
     */
    public function __construct(
        array $chain = []
    ) {
        $this->chain = $chain;
    }

    /**
     * @param $config
     * @return array
     */
    public function execute($config)
    {
        /** @var \Pointspay\Pointspay\Api\Data\StructureDataUpdaterInterface $chain */
        foreach ($this->chain as $link) {
            $config = $link->execute($config);
        }
        return $config;
    }
}
