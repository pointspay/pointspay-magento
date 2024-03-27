define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Pointspay_Pointspay/js/model/pointspay-configuration'
    ],
    function (
        Component,
        rendererList,
        config
    ) {
        'use strict';

        const availableMethods = config.availableMethodDetails;

        if (typeof availableMethods === 'object' && availableMethods !== null) {
            for (const prop in availableMethods) {
                if (Object.prototype.hasOwnProperty.call(availableMethods, prop)) {
                    const methodProp = availableMethods[prop];
                    if (methodProp.isActive) {
                        rendererList.push(
                            {
                                type: methodProp.code,
                                component: 'Pointspay_Pointspay/js/view/payment/method-renderer/pointspay-standalone',
                                config: {
                                    methodId: methodProp.code,
                                    title: methodProp.name,
                                    isBillingAddressRequired: true,
                                    logoUrl: methodProp.logo
                                }
                            }
                        );
                    }
                }
            }
        }
        return Component.extend({});
    }
);
