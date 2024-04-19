<?php

namespace Pointspay\Pointspay\Api;

interface IpnInterface
{
    const ORDER_ID = 'order_id';
    const PAYMENT_ID = 'payment_id';
    const STATUS = 'status';

    /**
     * @param array $gatewayData
     * @return mixed
     */
    public function processIpnRequest($gatewayData);
}
