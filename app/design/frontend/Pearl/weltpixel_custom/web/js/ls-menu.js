define([
        'jquery',
        'jquery/ui',
        'mage/menu'
    ],
function($){
    $.widget('ls.menu', $.mage.menu, {
        _toggleMobileMode: function() {
            $('.nav-sections-3 .customer-menu ul:not(.company,.custom-top-links) li:last').insertBefore('.nav-sections-3 .customer-menu ul:not(.company,.custom-top-links) li:eq(0)');
            return this._super(); // parent method will be called by _super()
        }
    });

    return $.ls.menu;
});

