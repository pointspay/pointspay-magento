<?php

namespace Pointspay\Pointspay\Gateway\Validator;

use Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response;
use Magento\Payment\Gateway\Validator\AbstractValidator;

class RefundMessageStructureValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        if (isset($validationSubject['response'])) {
            $response = $validationSubject['response'];
            if (!isset($response['body']['status']) || $response['body']['status'] != Response::ACCEPTED_REFUND_STATUS) {
                // this is only for old version of the PHP
                $message = !empty($response['body']['message']) ? $response['body']['message'] : null;
                $responseCode = isset($response['status_code']) ? $response['status_code'] : 'no_code';
                $code = !empty($response['body']['code']) ? $response['body']['code'] : $responseCode;
                return $this->createResult(false, [$message], [$code]);
            }
        }
        return $this->createResult(true);
    }
}
