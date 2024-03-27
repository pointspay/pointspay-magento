<?php

namespace Pointspay\Pointspay\Gateway\Validator;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Validator\Url as UrlValidator;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Pointspay\Pointspay\Service\Signature\Validator as SignatureValidator;

class HrefValidator extends AbstractValidator
{
    /**
     * @var \Magento\Framework\Validator\Url
     */
    private $urlValidator;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        UrlValidator $urlValidator
    ) {
        parent::__construct($resultFactory);
        $this->urlValidator = $urlValidator;
    }

    public function validate(array $validationSubject)
    {
        if (isset($validationSubject['response'])) {
            $response = $validationSubject['response'];
            if (strpos($response['body']['href'], 'http') !== false && $this->_isFullyQualifiedUrl(rtrim($response['body']['href'], '/') . '/')) {
                return $this->createResult(true);
            }
        }
        $message = __('Invalid url href provided');
        return $this->createResult(false, [$message], ['REDIRECT_URL_MISSING']);
    }

    private function _isFullyQualifiedUrl($value)
    {
        return preg_match('/\/$/', $value) && $this->getUrlValidator()->isValid($value, ['http', 'https']);
    }

    private function getUrlValidator()
    {
        if (!$this->urlValidator) {
            $this->urlValidator = ObjectManager::getInstance()->get(UrlValidator::class);
        }
        return $this->urlValidator;
    }
}
