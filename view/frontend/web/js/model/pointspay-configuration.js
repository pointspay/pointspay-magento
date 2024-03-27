define(function () {
    'use strict';

    const availableMethodDetails = window.checkoutConfig.payment.pointspay_available_methods_details,
        mainMethodCode = 'pointspay_required_settings',
        requiredSettings = window.checkoutConfig.payment[mainMethodCode];

    return {
        availableMethodDetails: availableMethodDetails || {},
        requiredSettings: requiredSettings || {},
        mainMethodCode
    };
});
