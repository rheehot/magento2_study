define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    var show_comment_blockConfig = window.checkoutConfig.show_comment_block;
    return Component.extend({
        defaults: {
            template: 'Codazon_OneStepCheckout/checkout/payment/additional-block'
        },
        canVisibleBlock: show_comment_blockConfig
    });
});