define([
    'jquery',
    'mage/storage',
    'jquery/jquery-storageapi'
], function ($) {

    'use strict';

    return {
        cacheKey: 'pointspay-storage-field',
        storage : $.initNamespaceStorage('pointspay-storage').localStorage,

        saveData : function (data) {
            this.storage.set(this.cacheKey, data);
        },

        /**
         * @return {*}
         */
        getData : function () {
            if (!this.storage.get(this.cacheKey)) {
                this.reset();
            }
            return this.storage.get(this.cacheKey);
        },

        setData: function ( key, data ) {
            const obj = this.getData();
            obj[key] = data;
            this.saveData(obj);
        },

        reset : function () {
            this.saveData({});
        }
    }
});
