/**
 * TUR-18 Add shipping Information
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/totals',
], function (Component, totals) {
    'use strict';

    return Component.extend({
        isLoading: totals.isLoading,
        shippingInformation : window.checkoutConfig.shippingInformation,
    });
});
