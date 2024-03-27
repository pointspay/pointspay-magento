<?php

namespace Pointspay\Pointspay\Api\Data;

interface OrderPaymentInterface
{
    const AMOUNT = 'amount';

    const TOTAL_CAPTURED = 'total_captured';

    const ORIGINAL_REFERENCE = 'original_reference';

    const PAYMENT_METHOD = 'payment_method';

    const PAYMENT_ID = 'payment_id';

    const TOTAL_REFUNDED = 'total_refunded';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const CAPTURE_STATUS = 'capture_status';

    // Either manual capture is not being used OR payment method does not support manual capture
    const CAPTURE_STATUS_AUTO_CAPTURE = 'Auto Captured';

    // Payment has been manually captured
    const CAPTURE_STATUS_MANUAL_CAPTURE = 'Manually Captured';

    // Payment has been partially manually captured
    const CAPTURE_STATUS_PARTIAL_CAPTURE = 'Partially Captured';

    // Payment has not been captured yet
    const CAPTURE_STATUS_NO_CAPTURE = 'Not captured';

    const ENTITY_ID = 'entity_id';
}
