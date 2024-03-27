define([
    'ko',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Pointspay_Pointspay/js/model/pointspay-token'
], function (ko, Component, quote, pointspayToken) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Pointspay_Pointspay/payment/pointspay-standalone',
            methodId: null,
            title: null,
            config: {
                isBillingAddressRequired: true
            }
        },

        getCode: function () {
            return this.methodId;
        },

        getTitle: function () {
            return this.title;
        },

        getLogo: function () {
            return this.logoUrl;
        },

        isChecked: ko.computed(function () {
            return quote.paymentMethod() ? quote.paymentMethod().method : null;
        }),

        getData: function () {
            return {
                // title:   this.getTitle(),
                method: this.getCode()
            };
        },
        placeOrder:function (data, event) {
            this.redirectAfterPlaceOrder = false;
            pointspayToken.placeOrder(this, this._super.bind(this), data, event);
        }
    });
});
