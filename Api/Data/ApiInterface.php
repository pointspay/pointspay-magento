<?php

namespace Pointspay\Pointspay\Api\Data;

interface ApiInterface
{
    const POINTSPAY_SUCCESS_SUFFIX = 'pointspay/api/success';
    const POINTSPAY_CANCEL_SUFFIX = 'pointspay/api/cancel';
    const POINTSPAY_FAIL_SUFFIX = 'pointspay/api/failure';
    const REST_IPN_SUFFIX = 'pointspay/api/ipn';
    /**
     * @return array|null
     */
    public function getPaymentMethods();
}
