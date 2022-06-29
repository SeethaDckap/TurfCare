/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/error-processor',
    'jquery',
    'Magento_Customer/js/model/customer'
], function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor, $, customer) {
    'use strict';

    return {
        /**
         * @param {Object} address
         */
        getRates: function (address) {
            var cache, self= this, customerShipVia = false, addressShipVia = false;

            shippingService.isLoading(true);
            cache = rateRegistry.get(address.getKey());

            if (cache) {
                shippingService.setShippingRates(cache);
                shippingService.isLoading(false);
            } else {
                storage.post(
                    resourceUrlManager.getUrlForEstimationShippingMethodsByAddressId(),
                    JSON.stringify({
                        addressId: address.customerAddressId
                    }),
                    false
                ).done(function (result) {
                    customerShipVia = self.getCustomerShipVia(customer);
                    console.log(customerShipVia);
                    if(customerShipVia) {
                        if(result.length > 1) {
                            result.splice(0,1);
                        }
                    }
                    else {
                        addressShipVia = self.getAddressShipVia(address);
                        if(addressShipVia) {
                            if(result.length > 1) {
                                result.splice(0,1);
                            }
                        }
                    }
                    
                    rateRegistry.set(address.getKey(), result);
                    shippingService.setShippingRates(result);
                }).fail(function (response) {
                    shippingService.setShippingRates([]);
                    errorProcessor.process(response);
                }).always(function () {
                    shippingService.isLoading(false);
                }
                );
            }
        },

        // Get ShipVia code from customer data
        getCustomerShipVia: function(customer) {
            var customerShipViaFlag = false, shipViaCode, self = this;
            $.each(customer.customerData.custom_attributes, function(key, value){
                if(key == 'ship_via') {
                    console.log();
                    shipViaCode = value.value;
                    if(shipViaCode == "PIU" || shipViaCode == "PIUC") {
                        customerShipViaFlag = true;
                    }
                }
            });

            return customerShipViaFlag;
        },

        // Get ShipVia code from customer address data
        getAddressShipVia: function(address) {
            var addressShipViaFlag = false, shipViaCode = null;

            $.each(address.customAttributes, function(key, value){
                if(key == 'sxe_ship_to') {
                    shipViaCode = value.value;
                    if(shipViaCode == "PIU" || shipViaCode == "PIUC") {
                        addressShipViaFlag = true;
                    }
                }
            });
            return addressShipViaFlag;
        }
    };
});
