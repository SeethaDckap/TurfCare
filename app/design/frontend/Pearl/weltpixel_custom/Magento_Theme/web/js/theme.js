/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'mage/smart-keyboard-handler',
        'matchMedia',
        'mage/mage',
        'mage/ie-class-fixer',
        'domReady!'
], function ($, keyboardHandler,mediaCheck) {
    'use strict';

    if ($('body').hasClass('checkout-cart-index')) {
        if ($('#co-shipping-method-form .fieldset.rates').length > 0 &&
            $('#co-shipping-method-form .fieldset.rates :checked').length === 0
        ) {
            $('#block-shipping').on('collapsiblecreate', function () {
                $('#block-shipping').collapsible('forceActivate');
            });
        }
    }

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');

    mediaCheck({
        media: '(max-width: 767px)',
        entry: $.proxy(function () {
            _toggleMobileMode();
        }, this)
    });

    function _toggleMobileMode(){
    if($("#store\\.links > .header.links") && $($("#store\\.links > .header.links")[0].children[0])) {
    var addElement =  document.createElement("ul");
    addElement.classList.add("header");
    addElement.classList.add("links");
    $(addElement).prepend($($("#store\\.links > .header.links")[0].children[1]).detach());
    $(addElement).prepend($($("#store\\.links > .header.links")[0].children[0]).detach());
    $('#store\\.menu').prepend(addElement);
    }
    }

    keyboardHandler.apply();
});