define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/full-screen-loader',
    'Pointspay_Pointspay/js/model/pointspay-service'
], function (
    $,
    wrapper,
    fullScreenLoader,
    pointspayService
) {
    'use strict';

    return function (shippingInformationAction) {

        return wrapper.wrap(shippingInformationAction, function (originalAction) {
            return originalAction().then(function (result) {
                fullScreenLoader.startLoader();
                pointspayService.retrievePaymentMethods().done(function(paymentMethods) {
                    pointspayService.setPaymentMethods(paymentMethods);
                    fullScreenLoader.stopLoader();
                }).fail(function() {
                    console.log('Fetching the payment methods failed!');
                    fullScreenLoader.stopLoader();
                });
                return result;
            });
        });

    };
});
