/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Codazon_OneStepCheckout/js/model/comment-assigner'
], function ($, wrapper, commentAssigner) {
    'use strict';

    return function (placeOrderService) {

        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderService, function (originalAction, serviceUrl, payload, messageContainer) {
            commentAssigner(payload);

            return originalAction(serviceUrl, payload, messageContainer);
        });
    };
});
