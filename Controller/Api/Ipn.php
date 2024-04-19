<?php

namespace Pointspay\Pointspay\Controller\Api;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Pointspay\Pointspay\Api\IpnInterface;
use Pointspay\Pointspay\Service\Checkout\Service;

class Ipn extends Action
{

    /**
     * @var Service
     */
    private $service;

    private $ipnModel;

    /**
     * @param Context $context
     * @param Service $service
     * @param IpnInterface $ipnModel
     */

    public function __construct(
        Context             $context,
        Service             $service,
        IpnInterface        $ipnModel
    ) {
        parent::__construct($context);
        $this->service     = $service;
        $this->ipnModel    = $ipnModel;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->getRequest()->isPut() && $ipnData = $this->validateIpnData($this->getRequest()->getContent())) {
            $this->ipnModel->processIpnRequest($ipnData);
            $this->service->logResponse(
                'IPN header authorization',
                ['authorization'=>$this->getRequest()->getHeader('authorization')]
            );
        } else {
            $this->service->logException('IPN Empty data');
        }
        return;
    }

    /**
     * @param string $ipnData
     * @return mixed
     */
    private function validateIpnData(string $ipnData = '')
    {
        $data = json_decode($ipnData, true);
        if(empty($data)) {
            return false;
        }
        if(!isset($data[IpnInterface::ORDER_ID])
            || !isset($data[IpnInterface::PAYMENT_ID])
            || !isset($data[IpnInterface::STATUS])
        ) {
            return false;
        }

        return $data;
    }
}
