/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/model/payment/method-list',
    'Codazon_OneStepCheckout/js/action/save-default-payment',
    'mage/translate'
], function (
    $,
    _,
    Component,
    ko,
    quote,
    stepNavigator,
    paymentService,
    methodConverter,
    getPaymentInformation,
    checkoutDataResolver,
    methodList,
    saveDefaultPayment,
    $t
) {
    'use strict';

    /** Set payment methods to collection */
    paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));

    return Component.extend({
        defaults: {
            template: 'Codazon_OneStepCheckout/payment',
            activeMethod: ''
        },
        isVisible: ko.observable(quote.isVirtual()),
        quoteIsVirtual: quote.isVirtual(),
        isPaymentMethodsAvailable: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length > 0;
        }),

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.navigate();
            
            return this;
        },

        showHide: function(element){
            var button = element.closest('.payment-method').find('.payment-method-content button.action.primary.checkout');
            if(button.length != 1 && element.closest('.payment-method').hasClass('_active')){ //is paypal button
                element.closest('.payment-method').find('.payment-method-content .checkout-agreements').show();
                $('#checkout_agreements_block').hide();
            }else{
                $('#checkout_agreements_block').show();
            }
        },

        isPaymentLoaded: function(){
            var payment = $("#checkout-payment-method-load input[name='payment[method]']");
            var self = this;
            if(payment.length){
                payment.each(function(){
                    self.showHide($(this));
                    $(this).on('click',function(){
                        self.showHide($(this));
                    });
                });
                $("#customer-email-error").hide();
            }else{
                setTimeout(function(){
                    self.isPaymentLoaded();
                },500);
            }
        },

        /**
         * Navigate method.
         */
        navigate: function () {
            var self = this;

            getPaymentInformation().done(function () {
                //self.isVisible(true);
                saveDefaultPayment();
                setTimeout(function(){
                    self.isPaymentLoaded();
                },500);

            });
        },

        /**
         * @return {*}
         */
        getFormKey: function () {
            return window.checkoutConfig.formKey;
        }
    });
});