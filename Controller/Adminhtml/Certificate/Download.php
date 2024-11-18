<?php

namespace Pointspay\Pointspay\Controller\Adminhtml\Certificate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Pointspay\Pointspay\Service\CertificateHandler;

class Download extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{

    /**
     * @var \Pointspay\Pointspay\Service\CertificateHandler
     */
    private $certificateHandler;

    public function __construct(
        Context $context,
        CertificateHandler $certificateHandler
    ) {
        parent::__construct($context);
        $this->certificateHandler = $certificateHandler;
    }

    public function execute()
    {
        $scopeId = $this->getRequest()->getParam('scope_id') ?: 0;
        $paymentMethodCode = $this->getRequest()->getParam('payment_method_code');

        $merchantOauthData = $this->certificateHandler->get($paymentMethodCode, $scopeId);
        $content = $merchantOauthData->getCertificate();
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(200);
        $result->setHeader('Content-Type', 'text/plain', true);
        $result->setContents($content);
        return $result;
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
