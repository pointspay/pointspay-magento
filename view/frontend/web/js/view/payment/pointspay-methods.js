define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_SalesRule/js/action/set-coupon-code',
        'Magento_SalesRule/js/action/cancel-coupon',
        'Pointspay_Pointspay/js/model/pointspay-service'
    ],
    function (
        Component,
        rendererList,
        fullScreenLoader,
        setCouponCodeAction,
        cancelCouponAction,
        pointspayService
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'pointspay_required_settings',
                component: 'Pointspay_Pointspay/js/view/payment/method-renderer/pointspay-payment'
            }
        );
        return Component.extend({
            initialize: function () {
                this._super();

                var retrievePaymentMethods = function (){
                    fullScreenLoader.startLoader();

                    pointspayService.retrievePaymentMethods().done(function(paymentMethods) {
                        pointspayService.setPaymentMethods(paymentMethods);
                        fullScreenLoader.stopLoader();
                    }).fail(function() {
                        console.log('Fetching the payment methods failed!');
                        fullScreenLoader.stopLoader();
                    });
                };
                retrievePaymentMethods();
                //Retrieve payment methods to ensure the amount is updated, when applying the discount code
                setCouponCodeAction.registerSuccessCallback(function () {
                    retrievePaymentMethods();
                });
                //Retrieve payment methods to ensure the amount is updated, when canceling the discount code
                cancelCouponAction.registerSuccessCallback(function () {
                    retrievePaymentMethods();
                });
            }
        });
    }
);
