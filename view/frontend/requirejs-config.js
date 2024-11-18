/*jshint browser:true jquery:true*/
/*global alert*/
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Pointspay_Pointspay/js/model/set-shipping-information-mixin': true
            }
        }
    }
};
