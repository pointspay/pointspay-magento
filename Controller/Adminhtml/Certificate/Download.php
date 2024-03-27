<?php

namespace Pointspay\Pointspay\Controller\Adminhtml\Certificate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
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
        if (strpos($paymentMethodCode, '_required_settings') === false) {
            $paymentMethodCode .= '_required_settings';
        }
        $merchantOauthData = $this->certificateHandler->get($paymentMethodCode, $scopeId);
        $content = $merchantOauthData->getCertificate();
        $this->header('Content-Type: text/plain', true);
        echo $content;
        $this->exit(0);
    }

    protected function header($header, $replace = true, $httpResponseCode = 0)
    {
        header($header, $replace, $httpResponseCode);
        return $this;
    }

    protected function exit($code)
    {
        exit($code);
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
