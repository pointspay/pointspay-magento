require([
    'jquery',
    'Pointspay_Pointspay/js/pointspay-storage',
    'domReady!'
], function ($, pointspayStorage) {
    'use strict';

    const POINSTPAY_GROUP = 'payment_us_pointspay_group_all_in_one';

    function isPointsPayOpenByDefault(){
        const storageData = pointspayStorage?.getData();
        return storageData?.openByDefault;
    }

    $(document).ready(function () {
        if(isPointsPayOpenByDefault()){
            $('button#' + POINSTPAY_GROUP + '-head').click();

            $('html, body').animate({
                scrollTop: $('a#' + POINSTPAY_GROUP + '_pointspay_required_settings-head').offset()?.top
            }, 500);

            pointspayStorage.reset();
        }
    });
});
