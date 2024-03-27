<?php

namespace Pointspay\Pointspay\Api;

interface IpnInterface
{
    const ORDER_ID = 'order_id';
    const PAYMENT_ID = 'payment_id';
    const STATUS = 'status';

    /**
     * @param array $ipnData
     * @return mixed
     */
    public function processIpnRequest($ipnData);
}
