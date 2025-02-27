define([
    'jquery',
    'mage/utils/wrapper',
    'Pointspay_Pointspay/js/model/pointspay-service'
], function (
    $,
    wrapper,
    pointspayService
) {
    'use strict';

    return function (shippingInformationAction) {

        return wrapper.wrap(shippingInformationAction, function (originalAction) {
            return originalAction().then(function (result) {
                pointspayService.startLoader();
                pointspayService.retrievePaymentMethods().done(function(paymentMethods) {
                    pointspayService.setPaymentMethods(paymentMethods);
                    pointspayService.stopLoader();
                }).fail(function() {
                    console.log('Fetching the payment methods failed!');
                    pointspayService.stopLoader();
                });
                return result;
            });
        });

    };
});
