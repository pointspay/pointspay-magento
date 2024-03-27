<?php

namespace Pointspay\Pointspay\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Pointspay\Pointspay\Service\Signature\Validator as SignatureValidator;

class ResponseSignatureValidator extends AbstractValidator
{
    /**
     * @var SignatureValidator
     */
    private $signatureValidator;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SignatureValidator $signatureValidator
    )
    {
        parent::__construct($resultFactory);
        $this->signatureValidator = $signatureValidator;
    }

    public function validate(array $validationSubject)
    {
        if (isset($validationSubject['response'])) {
            $response = $validationSubject['response'];
            if (!$this->signatureValidator->validate($response)) {
                $message = __('OAuth\'s signature verification failed.');
                return $this->createResult(false, [$message], ['SIGNATURE_VERIFICATION_FAILED']);
            }
        }
       return $this->createResult(true);
    }
}
