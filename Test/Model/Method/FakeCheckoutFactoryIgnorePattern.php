<?php

namespace Pointspay\Pointspay\Test\Model\Method;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Pointspay\Pointspay\Service\Refund\CheckoutFactory;

class FakeCheckoutFactoryIgnorePattern extends CheckoutFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;


    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = '\\Pointspay\\Pointspay\\Test\\Model\\Method\\FakeVirtualRefundService'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        parent::__construct($objectManager, $instanceName);
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Eav\Model\Entity
     */
    public function create(array $data = [])
    {

        $objectManagerHelper = new \Pointspay\Pointspay\Test\MageObjectManager();
        $checkoutService = $objectManagerHelper->objectManager->create(\Pointspay\Pointspay\Test\Model\Method\FakeRefund::class);
        $data['checkoutService'] = $checkoutService;
        return $this->_objectManager->create($this->_instanceName, $data);
    }

}
