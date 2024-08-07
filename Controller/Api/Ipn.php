<?php

namespace Pointspay\Pointspay\Controller\Api;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
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
     * @return ResultInterface
     */
    public function execute() : ResultInterface
    {
        if ($this->ipnValidator->validate($this->getRequest())) {
            $ipnData = json_decode($this->getRequest()->getContent(), true);
            try {
                $this->ipnModel->processIpnRequest($ipnData);
            } catch (\Exception $e) {
                $this->service->logException($e->getMessage());
                $this->service->logException($e->getTraceAsString());
                $this->service->logResponse(
                    'IPN header authorization',
                    ['authorization'=>$this->getRequest()->getHeader('authorization')]
                );
                $rawResult = $this->resultFactory->create(ResultFactory::TYPE_RAW);
                $rawResult->setHttpResponseCode(200);
                $rawResult->setContents('Transaction was finalized based on redirect');
                return $rawResult;
            }
        } else {
            $rawResult = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $rawResult->setHttpResponseCode(400);
            $rawResult->setContents('Invalid signature data');
            return $rawResult;
        }
        $rawResult = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $rawResult->setHttpResponseCode(200);
        $rawResult->setContents('IPN data successfully processed');
        return $rawResult;
    }

}
