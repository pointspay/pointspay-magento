<?php

namespace Pointspay\Pointspay\Block\Adminhtml\System\Config;

/**
 * Class SystemConfigDefaults
 *
 * This class provides system values for Pointspay configuration fields
 * in case the values are not explicitly set in the configuration.
 */
class SystemConfigDefaults
{
    /**
     * Predefined default values for various configuration fields.
     *
     * @var array
     */
    private $defaultValues = [
        'active' => 0,
        'model' => 'PointspayPaymentVirtualFacade',
        'title' => 'Pointspay',
        'allowspecific' => 0,
        'sort_order' => 1,
        'payment_action' => 'initialize',
        'is_gateway' => 1,
        'can_use_checkout' => 1,
        'can_use_internal' => 1,
        'can_refund_partial_per_invoice' => 1,
        'can_refund' => 1,
        'can_void' => 1,
        'can_cancel' => 1,
        'can_initialize' => 1,
        'group' => 'pointspay',
        'demo_mode' => 0,
        'request_timeout' => 30,
        'api_key' => 'No ApiKey',
    ];

    /**
     * Retrieve the default value for a specified configuration field.
     *
     * @param string $field The key for the configuration value.
     * @return mixed The default value for the specified field, or null if not found.
     */
    public function getDefaultValue($field)
    {
        return $this->defaultValues[$field] ?? null;
    }

}
