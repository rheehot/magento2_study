/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    registry,
    $t,
    selectBillingAddress
) {
    'use strict';

    var popUp = null;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item'
        },
        default_shipping_carrier: ko.observable(window.checkoutConfig.default_shipping),
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),

        initObservable: function () {
        	var lastSelectedBillingAddress = null,
		        newAddressOption = {
		            /**
		             * Get new address label
		             * @returns {String}
		             */
		            getAddressInline: function () {
		                return $t('New Address');
		            },
		            customerAddressId: null
		        },
		        addressOptions = addressList().filter(function (address) {
		            return address.getType() == 'customer-address'; //eslint-disable-line eqeqeq
		        });

		    addressOptions.push(newAddressOption);

            this._super().
                observe(['paymentGroupsList']);
            this._super()
                .observe({
                    isAddressSameAsShipping: true,
                    isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length === 1,
                    isAddressDetailsVisible: quote.billingAddress() != null,
                });
            return this;
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';
            this._super();
            self.hasShippingMethod = ko.pureComputed(function(){
                var hasMethod = false;
                if(quote.shippingMethod()){
                    var stillAvailable = self.isShippingOnList(quote.shippingMethod().carrier_code,quote.shippingMethod().method_code);
                    hasMethod = (stillAvailable)?true:false;
                }
                return hasMethod;
            }),
            quote.shippingMethod.subscribe(function () {
                self.errorValidationMessage(false);
            });
            
            shippingService.getShippingRates().subscribe(function(){
                if(self.hasShippingMethod() == true){
                    self.selectShippingMethod(quote.shippingMethod()); //select a shipping method after shipping method loaded 
                }else{
                    var method = self.getDefaultMethod();
                    if(method !== false){
                        self.selectShippingMethod(method);
                    }
                }
            });

            return this;
        },

        isShippingOnList: function(carrier_code,method_code){
            var list = this.getShippingList();
            if(list.length > 0){
                var carrier = ko.utils.arrayFirst(list, function(carrier) {
                    return (carrier.code == carrier_code);
                });
                if(carrier && carrier.methods.length > 0){
                    var method = ko.utils.arrayFirst(carrier.methods, function(method) {
                        return (method.method_code == method_code);
                    });
                    return (method)?true:false;
                }else{
                    return false;
                }
            }
            return false;
        },

        getShippingList: function () {
            var list = [];
            var rates = this.rates();
            if(rates && rates.length > 0){
                ko.utils.arrayForEach(rates, function(method) {
                    if(list.length > 0){
                        var notfound = true;
                        ko.utils.arrayForEach(list, function(carrier) {
                            if(carrier && carrier.code == method.carrier_code){
                                carrier.methods.push(method);
                                notfound = false;
                            }
                        });
                        if(notfound == true){
                            var carrier = {
                                code:method.carrier_code,
                                title:method.carrier_title,
                                methods:[method]
                            }
                            list.push(carrier);
                        }
                    }else{
                        var carrier = {
                            code:method.carrier_code,
                            title:method.carrier_title,
                            methods:[method]
                        }
                        list.push(carrier);
                    }
                });
            }
            return list;
        },

        getDefaultMethod: function(){
            var self = this;
            var list = this.getShippingList();
            if(list.length > 0){
                var carrier = ko.utils.arrayFirst(list, function(data) {
                    return (self.default_shipping_carrier())?(data.code == self.default_shipping_carrier()):true;
                });
                if(carrier && carrier.methods.length > 0){
                    var method = ko.utils.arrayFirst(carrier.methods, function() {
                        return true;
                    });
                    return (method)?method:false;
                }else{
                    return false;
                }
            }
            return false;
        },

        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) { // trigger when click on shipping method and after change shipping address
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
            if(!customer.placeorder){
                if($("#co-payment-form ._active input[type='checkbox']").prop('checked') == true || $("#co-payment-form .billing-address-same-as-shipping-block input[type='checkbox']").prop('checked') == true){
                    quote.billingAddress(quote.shippingAddress());//update shippingAddress to billing address on payment step, this trigger update text on address of payment step
                }
                setShippingInformationAction();//save seleted shipping method and update payment method from ajax
            }
            return true;
        },

        //billing-address function
        updateAddresses: function () {
            if (window.checkoutConfig.reloadOnBillingAddress ||
                !window.checkoutConfig.displayBillingOnPaymentMethod
            ) {
                setBillingAddressAction(globalMessageList);
            }
        },

        isAddressSameAsShipping: function(){
            if($("#co-payment-form ._active input[type='checkbox']").prop('checked') == true || $("#co-payment-form .billing-address-same-as-shipping-block input[type='checkbox']").prop('checked') == true){
                return true;
            }else{
                return false;
            }
        },

        //====== end of billing-address function ====

        /**
         * Set shipping information handler
         */
        setShippingInformation: function () { //handle "next" button on default checkout to payment step, this will be triggered when click "place order"
            var self = this;
            if (this.validateShippingInformation()) {
                setShippingInformationAction().done(function(){
                    //self.useShippingAddress();
                    if(customer.placeorder){
                        //self.useShippingAddress();
                        $("#co-payment-form ._active button[type='submit']").click(); // trigger real "place order" button
                    }
                });
            }else{
                customer.placeorder = false;
            }
        },

        /**
         * @return {Boolean}
         */
        validateShippingInformation: function () {
            var shippingAddress,
                addressData,
                loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn(),
                field;

            if (!quote.shippingMethod()) {
                this.errorValidationMessage($t('Please specify a shipping method.'));

                return false;
            }

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }

            if (this.isFormInline) {
                this.source.set('params.invalid', false);
                this.triggerShippingDataValidateEvent();

                if (emailValidationResult &&
                    this.source.get('params.invalid') ||
                    !quote.shippingMethod()['method_code'] ||
                    !quote.shippingMethod()['carrier_code']
                ) {
                    this.focusInvalid();

                    return false;
                }

                shippingAddress = quote.shippingAddress();
                addressData = addressConverter.formAddressDataToQuoteAddress(
                    this.source.get('shippingAddress')
                );

                //Copy form data to quote shipping address object
                for (field in addressData) {
                    if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    shippingAddress['save_in_address_book'] = 1;
                }
                selectShippingAddress(shippingAddress);
            }

            if (!emailValidationResult) {
                $(loginFormSelector + ' input[name=username]').focus();

                return false;
            }

            return true;
        },

        /**
         * Trigger Shipping data Validate Event.
         */
        triggerShippingDataValidateEvent: function () {
            this.source.trigger('shippingAddress.data.validate');

            if (this.source.get('shippingAddress.custom_attributes')) {
                this.source.trigger('shippingAddress.custom_attributes.data.validate');
            }
        }
    });
});
