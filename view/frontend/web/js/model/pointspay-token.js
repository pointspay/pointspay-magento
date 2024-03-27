define([
    'jquery',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'mage/url',
    'Magento_Checkout/js/model/quote'
], function ($, customer, urlBuilder, storage, url, quote) {
    'use strict';

    return {
        redirectUrl: '',

        getMerchantAppAndRedirect() {
            let restUrl;
            const quoteId = quote.getQuoteId();

            if (customer.isLoggedIn()) {
                restUrl = urlBuilder.createUrl('/carts/mine/get-merchant-app-href', {}) +
                    '?quoteId=' + quoteId +
                    '&form_key=' + jQuery.cookie('form_key') +
                    '&isAjax=true';
            } else {
                restUrl = urlBuilder.createUrl('/guest-carts/:quoteId/get-merchant-app-href', {
                        quoteId: quoteId
                    }) +
                    '?form_key=' + jQuery.cookie('form_key') +
                    '&isAjax=true';
            }

            const promise = storage.get(restUrl);

            promise.done(function (response) {
                window.location = response;
            });
        },
        placeOrder: function (originalContext, originalPlaceOrder, data, event) {
            const self = this;

            if (event) {
                event.preventDefault();
            }

            if (originalContext.isPlaceOrderActionAllowed() === true) {
                originalContext.isPlaceOrderActionAllowed(false);

                originalContext.getPlaceOrderDeferredObject()
                    .done(
                        function () {
                            self.afterPlaceOrder();
                        }
                    ).always(
                    function () {
                        originalContext.isPlaceOrderActionAllowed(true);
                    }
                );

                return true;
            }

            return false;
        },
        afterPlaceOrder: function () {
            this.getMerchantAppAndRedirect();
        }
    };
});
