<?php

namespace Pointspay\Pointspay\Controller\Adminhtml\Refresh;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Pointspay\Pointspay\Service\PaymentMethodsUpdater;

class Payments extends \Magento\Backend\App\Action implements HttpPostActionInterface, CsrfAwareActionInterface
{

    /**
     * @var \Pointspay\Pointspay\Service\PaymentMethodsUpdater
     */
    private $paymentMethodsUpdater;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Pointspay\Pointspay\Service\PaymentMethodsUpdater $paymentMethodsUpdater
     */
    public function __construct(
        Context $context,
        PaymentMethodsUpdater $paymentMethodsUpdater
    ) {
        parent::__construct($context);

        $this->paymentMethodsUpdater = $paymentMethodsUpdater;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        try {
            $this->paymentMethodsUpdater->execute();
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => true,
                'message' => 'Payments refreshed'
            ]);
        } catch (Exception $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
