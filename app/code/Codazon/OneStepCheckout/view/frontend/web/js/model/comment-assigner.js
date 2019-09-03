/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global alert*/
define([
    'jquery'
], function ($) {
    'use strict';
    /** Override default place order action and add agreement_ids to request */
    return function (paymentData) {
        var comments = jQuery('[name="comment-code"]:first').val()

        paymentData['comments'] = comments;
    };
});
