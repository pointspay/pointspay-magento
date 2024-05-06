<?php

namespace Pointspay\Pointspay\Controller\Api;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Pointspay\Pointspay\Api\IpnInterface;
use Pointspay\Pointspay\Service\Checkout\Service;
use Pointspay\Pointspay\Service\Signature\IpnValidator;

class Ipn extends Action
{

    /**
     * @var Service
     */
    private $service;

    private $ipnModel;
    private $ipnValidator;

    /**
     * @param Context $context
     * @param Service $service
     * @param IpnInterface $ipnModel
     */

    public function __construct(
        Context             $context,
        Service             $service,
        IpnInterface        $ipnModel,
        IpnValidator        $ipnValidator
    ) {
        parent::__construct($context);
        $this->service     = $service;
        $this->ipnModel    = $ipnModel;
        $this->ipnValidator= $ipnValidator;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->ipnValidator->validate($this->getRequest())) {
            $ipnData = json_decode($this->getRequest()->getContent(), true);
            $this->ipnModel->processIpnRequest($ipnData);
            $this->service->logResponse(
                'IPN header authorization',
                ['authorization'=>$this->getRequest()->getHeader('authorization')]
            );
        } else {
            $this->service->logException('IPN Invalid data');
        }
        return;
    }

}
