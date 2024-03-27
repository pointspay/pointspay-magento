<?php

namespace Pointspay\Pointspay\Gateway\Data\Order;

/**
 * Factory class for @see \Pointspay\Pointspay\Gateway\Data\Order\OrderAdapter
 */
class OrderAdapterFactory extends \Magento\Payment\Gateway\Data\Order\OrderAdapterFactory
{
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Pointspay\\Pointspay\\Gateway\\Data\\Order\\OrderAdapter')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        parent::__construct($objectManager, $instanceName);
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Payment\Gateway\Data\Order\OrderAdapter
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
