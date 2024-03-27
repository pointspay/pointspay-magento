<?php

namespace Pointspay\Pointspay\Gateway\Validator;

use Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response;
use Magento\Payment\Gateway\Validator\AbstractValidator;

class MessageStructureValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        if (isset($validationSubject['response'])) {
            $response = $validationSubject['response'];
            if (!isset($response['body']['status']) || $response['body']['status'] != Response::ACCEPTED_STATUS) {
                // this is only for old version of the PHP
                $message = !empty($response['body']['message']) ? $response['body']['message'] : null;
                $code = !empty($response['body']['code']) ? $response['body']['code'] : $response['status_code'];
                return $this->createResult(false, [$message], [$code]);
            }
        }
        return $this->createResult(true);
    }
}
