define([
    'ko',
    'mage/storage',
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'mage/cookies'
], function (
    ko,
    storage,
    $,
    _,
    quote,
    customer,
    urlBuilder,
) {
    'use strict';

    return {
        paymentMethods: ko.observable({}),

        /**
         * Retrieve the list of available payment methods from Pointspay
         */
        retrievePaymentMethods: function() {
            // url for guest users
            var poinspayUrl = urlBuilder.createUrl(
                '/pointspay/guest-carts/:cartId/retrieve-pointspay_payment-methods', {
                    cartId: quote.getQuoteId(),
                });

            // url for logged in users
            if (customer.isLoggedIn()) {
                poinspayUrl = urlBuilder.createUrl(
                    '/pointspay/carts/mine/retrieve-pointspay_payment-methods', {});
            }

            return storage.post(
                poinspayUrl,
                JSON.stringify({
                    cartId: quote.getQuoteId(),
                    form_key: $.mage.cookies.get('form_key')
                })
            );
        },

        getPaymentMethods: function() {
            return this.paymentMethods;
        },

        setPaymentMethods: function(paymentMethods) {
            this.paymentMethods(paymentMethods);
        }
    };
});
