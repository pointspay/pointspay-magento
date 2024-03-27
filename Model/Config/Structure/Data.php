<?php

namespace Pointspay\Pointspay\Model\Config\Structure;

use Exception;
use Magento\Config\Model\Config\Structure\Data as StructureData;
use Pointspay\Pointspay\Model\Config\Structure\Data\DataChain;
use Psr\Log\LoggerInterface;

class Data
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Pointspay\Pointspay\Model\Config\Structure\Data\DataChain
     */
    private $dataChain;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Pointspay\Pointspay\Model\Config\Structure\Data\DataChain $dataChain
     */
    public function __construct(
        LoggerInterface $logger,
        DataChain $dataChain
    ) {
        $this->logger = $logger;
        $this->dataChain = $dataChain;
    }

    /**
     * Without this plugin the save config will not save data
     * @param \Magento\Config\Model\Config\Structure\Data $subject
     * @param callable $proceed
     * @param array $config
     * @return void
     */
    public function aroundMerge(StructureData $subject, callable $proceed, array $config): void
    {
        try {
            $newConfig = $this->dataChain->execute($config);
        } catch (Exception $e) {
            $newConfig = null;
            $this->logger->addCritical($e->getMessage(), ['old_config' => $config, 'new_config' => $newConfig ?: 'null']);
            $this->logger->addCritical($e->getTraceAsString());
        }
        $proceed($newConfig ?? $config);
    }
}
