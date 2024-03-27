<?php

namespace Pointspay\Pointspay\Service;

use Magento\Framework\Config\DataInterface;

class PaymentsReader
{
    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    private $dataStorage;

    /**
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     */
    public function __construct(DataInterface $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    /**
     * @return mixed
     */
    public function getAvailablePointspayMethods()
    {
        return $this->dataStorage->get('pointspay_methods', []);
    }

    /**
     * @return void
     */
    public function resetStorage()
    {
        $this->dataStorage->reset();
    }
}
