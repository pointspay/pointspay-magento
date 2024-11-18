define([
    'Magento_Checkout/js/view/payment/default',
    'ko',
    'jquery',
    'underscore',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/full-screen-loader',
    'Pointspay_Pointspay/js/model/pointspay-service',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Pointspay_Pointspay/js/model/pointspay-token'
], function (
    Component,
    ko,
    $,
    _,
    selectPaymentMethodAction,
    setPaymentInformationAction,
    errorProcessor,
    quote,
    checkoutData,
    fullScreenLoader,
    pointspayService,
    additionalValidators,
    pointspayToken
) {
    'use strict';

    var selectedProduct = ko.observable(null),
        pointspayPaymentMethods = ko.observable([]),
        canReloadPayments = ko.observable(true);

    return Component.extend({
        defaults: {
            template: 'Pointspay_Pointspay/payment/pointspay-view'
        },

        initObservable: function () {
            this._super().observe([
                'selectedProduct',
                'pointspayPaymentMethods'
            ]);
            return this;
        },

        initialize: function () {
            var self = this;
            this._super();

            fullScreenLoader.startLoader();

            var paymentMethodsObserver = pointspayService.getPaymentMethods();

            // Subscribe to any further changes (shipping address might change on the payment page)
            paymentMethodsObserver.subscribe(function (paymentMethodsResponse) {
                self.loadPointspayPaymentMethods(paymentMethodsResponse);
            });

            self.loadPointspayPaymentMethods(paymentMethodsObserver());

            quote.billingAddress.subscribe(function (address) {
                if (!address || !canReloadPayments()) {
                    return;
                }

                canReloadPayments(false);

                fullScreenLoader.startLoader();

                pointspayService.retrievePaymentMethods().done(function (paymentMethods) {
                    pointspayService.setPaymentMethods(paymentMethods);
                    fullScreenLoader.stopLoader();

                }).fail(function () {
                    console.log('Fetching the payment methods failed!');
                }).always(function () {
                    fullScreenLoader.stopLoader();
                    canReloadPayments(true);
                });
            }, this);
        },

        loadPointspayPaymentMethods: function (paymentMethodsResponse) {
            var self = this;

            self.pointspayPaymentMethods(paymentMethodsResponse);
            pointspayPaymentMethods(paymentMethodsResponse);

            fullScreenLoader.stopLoader();
        },

        getCode: function () {
            return 'pointspay_required_settings';
        },

        isVisible: function () {
            return true;
        },

        getSelectedProduct: ko.computed(function () {
            if (!quote.paymentMethod()) {
                return null;
            }

            if (quote.paymentMethod().method === 'pointspay_required_settings') {
                return selectedProduct();
            }

            return null;
        }),

        selectProduct: function () {
            var self = this;

            selectedProduct(self.code);

            // set payment method to sequra_payment
            var data = {
                'method': 'pointspay_required_settings',
                'po_number': null,
                'additional_data': {
                    'pointspay_flavor': selectedProduct()
                },
            };

            selectPaymentMethodAction(data);
            checkoutData.setSelectedPaymentMethod('pointspay_required_settings');

            return true;
        },

        getData: function () {
            var data = {
                'method': 'pointspay_required_settings',
                'po_number': null,
                'additional_data': {
                    'pointspay_flavor': selectedProduct()
                },
            };

            return data;
        },

        placeOrder:function (data, event) {
            this.redirectAfterPlaceOrder = false;
            pointspayToken.placeOrder(this, this._super.bind(this), data, event);
        },

    });
});
