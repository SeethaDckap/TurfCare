require([
    "jquery",
    "mage/url",
    "mage/mage",
    "mage/apply/main",
    "Magento_Ui/js/modal/alert",
    "accordion",
    'mage/tabs',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($, urlBuilder, mage, main) {
    $("#horizontal_tabs").tabs();
    $(document).ready(function () {
        getSkuSuggestions();

        var suggestionUrl = $('#id-items0sku').attr('data-suggestion-url');
        $('.firstItem.Item-0 .actions-toolbar').hide();
        if ($('#id-items0sku').val() === '') {
            $('.firstItem.Item-0 .actions-toolbar .clear_field').css("display", "none");
        }

        $('#customer_sku_csv').val();
        var wrapper = $(".box.box-items .fieldset");

        var add_button = $(".add-field-button");
        var x = 1;

        var defaultMinQty = $('#defaultMinQty').val();
        var defaultMaxQty = $('#defaultMaxQty').val();

        $(add_button).click(function (e) {
            e.preventDefault();
            $('.firstItem.Item-0 .actions-toolbar').show();
            $(wrapper).append('<div class="fields additional Item-' + x + '">' +
                '<input type="hidden" name="product-type" class="product-type" value=""> <input type="hidden" name="parent-class-id" class="parent-class-id" value=""> <input type="hidden" name="item-id" class="item-id" value="">' +
                '<div class="field sku required"><label class="label" for="id-items' + x + 'sku"><span>SKU</span></label><div class="control"><input class="input-text sku-value" type="text" autocomplete="off" data-validate="{required:true}" data-old-productsku="" value=""  aria-required="true" data-sku="true" data-id="' + x + 'sku" data-item=' + x + ' id="id-items' + x + 'sku" name="items[' + x + '][sku]">\n\
                <div class="sku-suggestions sku-suggestions-' + x + '" style="display: none;"></div>\n\
                </div> </div><div class="field qty required"><label class="label" for="id-items' + x + 'qty"><span>Qty</span></label>\n\
                <div class="control"><input type="number" data-validate="{required:true ,&quot;validate-number&quot;: true,&quot;validate-greater-than-zero&quot;: true,&quot;validate-number-range&quot;: &quot;0.0001-99999999.9999&quot;}"  aria-required="true" maxlength="13" class="qty" value="' + defaultMinQty + '" id="id-items' + x + 'qty" name="items[' + x + '][qty]"></div>\
                <note id="note-item-' + x + '"><span>' + $.mage.__("Min: ") + defaultMinQty + ' </span><span style="white-space: nowrap;">' + $.mage.__("Max: ") + defaultMaxQty + ' </span></note>\n\
                </div>\n\
                <div class="product-thumbnail" id="product-thumbnail-' + x + '" style="display: none;"><img src="" /></div>\n\
                <div class="field product-price price-container-' + x + '" style="display: none;">\n\
                    <label class="label" for="id-items' + x + 'price"><span>' + $.mage.__("Price") + '</span></label>\n\
                    <div class="obs-price product-price-' + x + '"></div>\n\
                </div>\n\
                <div class="field obs-product-info name-container-' + x + '" style="display: none;">\n\
                    <label class="label" for="id-items' + x + 'name"><span>' + $.mage.__("Name") + '</span></label>\n\
                    <div class="obs-product-name product-name-' + x + '"></div>\n\
                </div>\n\
                <div class="field obs-product-detail">\n\
                    <div class="product-sku-details product-item-' + x + '"></div>\n\
                </div>\n\
                <div class="product-item-details config-options product-options-wrapper orderbysku-pro-opt" data-itemnumber=' + x + ' id="product-options-wrapper-' + x + '" class="product-custom-option" style="display: none"></div>\n\
                <div class="actions-toolbar"><div class="secondary"><button data-role="delete" title="Remove Row" class="action remove no-display remove_field" rel="' + x + '" type="button"><span>Remove Row</span></button></div></div>\n\
                ');
            x++;
            getSkuSuggestions();
            main.apply();
        });

        $(wrapper).on("click", ".remove_field", function (e) {
            e.preventDefault();
            var itmerel = $(this).attr('rel');
            $('.fields.Item-' + itmerel + '').remove();
        });

        $(wrapper).on("click", ".clear_field", function (e) {
            e.preventDefault();
            $('#id-items0sku').val('');
            $('.product-sku-details.product-item-0').empty();
            if ($('#id-items0sku').val() === '') {
                $('.firstItem.Item-0 .actions-toolbar .clear_field').css("display", "none");
            }
        });

        function getSkuSuggestions() {
            var currentRequest = null;

            $(".sku-value").on("keyup", function () {

                var suggestionHtml;
                var itemnumber = $(this).attr('data-item');
                if ($(this).val() != '' && $(this).val().length > 2) {
                    var productsku = $(this).val();
                    productsku = productsku.trim();
                    var oldProductSku = $(this).attr('data-old-productsku');
                    if (oldProductSku === productsku) {
                        return false;
                    }

                    currentRequest = $.ajax({
                        showLoader: true,
                        url: suggestionUrl,
                        ifModified: true,
                        beforeSend: function () {
                            if (currentRequest != null) {
                                currentRequest.abort();
                            }
                        },
                        data: {string: $(this).val()},
                        type: "POST",
                        global: false,
                        dataType: 'json',
                        success: function (response) {
                            if (response.html.size > 0) {
                                suggestionHtml = '';
                                $(response.sku_array).each(function () {
                                    suggestionHtml += '<div class="item">';
                                    suggestionHtml += '<div class="image"><img src="' + $(this)[0]['image'] + '" width="100" height="100" /></div>';
                                    suggestionHtml += '<div class="obs-item-details">';
                                    suggestionHtml += '<div class="product-name">' + $(this)[0]['name'] + '</div>';
                                    suggestionHtml += '<div class="sku">' + $(this)[0]['sku'] + '</div>';
                                    suggestionHtml += '<div class="price-container">' + $(this)[0]['price'] + '</div>';
                                    suggestionHtml += '</div>';
                                    suggestionHtml += '</div>';
                                });
                                $('.sku-suggestions-' + itemnumber).show();
                                $('.sku-suggestions-' + itemnumber).html(suggestionHtml);
                                $('.sku-suggestions-' + itemnumber + ' .item').on('click', function () {
                                    $(this).parent().parent().find('.sku-value').val($(this).find('.sku').text());
                                    $(this).parent().parent().find('input.qty').focus();
                                    $(this).parent().parent().find('.sku-value').trigger('focusout');
                                    var textBoxValue = $(this).find('.sku').text();
                                    getProductDetails(textBoxValue, itemnumber);
                                    $('.sku-suggestions-' + itemnumber).html('');
                                    $('.sku-suggestions-' + itemnumber).hide();
                                });
                            } else {
                                $('.sku-suggestions-' + itemnumber).html('');
                                $('.sku-suggestions-' + itemnumber).hide();
                            }
                        }
                    });
                }
                if ($(this).val().length < 2) {
                    $('.sku-suggestions-' + itemnumber).html('');
                    $('.sku-suggestions-' + itemnumber).hide();
                }
            });
        }

        function getProductDetails(productSku, itemnumber) {
            var ajaxUrl = urlBuilder.build('orderbysku/customer/product');
            $('#product-options-wrapper-' + itemnumber).html('');
            if ($('#id-items0sku').val() === '') {
                $('.firstItem.Item-' + itemnumber + ' .actions-toolbar .clear_field').css("display", "none");
            } else {
                $('.firstItem.Item-' + itemnumber + ' .actions-toolbar .clear_field').css("display", "block");
            }

            var currentRequest = null;
            currentRequest = $.ajax({
                showLoader: true,
                url: ajaxUrl,
                data: {sku: productSku},
                ifModified: true,
                beforeSend: function () {
                    if (currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    if (data == '') {
                        $('.product-item-' + itemnumber).html('');
                        $('.product-name-' + itemnumber).html('');
                        $('.product-price-' + itemnumber).html('');
                        $('.product-item-' + itemnumber).find('.filter-options-content-obs').html('');
                        $('#product-thumbnail-' + itemnumber + ' img').attr('src', '');
                        $('.sku-suggestions').html('');
                        $('.sku-suggestions').hide();
                        $('.price-container-' + itemnumber).hide();
                        $('.name-container-' + itemnumber).hide();
                        return true;
                    }

                    if (!$.isEmptyObject(data)) {
                        var shortdescription = '';
                        var name;
                        var updatedName;
                        var cnfname;
                        var updatedCnfName;
                        var id;
                        var updatedId;
                        var replacedName;
                        var updatedQtyName;
                        var validate;
                        var updateValidate;
                        var inputType;

                        $('.sku-suggestions').html('');
                        $('.sku-suggestions').hide();

                        if (data.shortdescription) {
                            shortdescription = data.shortdescription;
                        }

                        $('.product-name-' + itemnumber).html(data.name);
                        $('.product-price-' + itemnumber).html(data.price_html);
                        $('.price-container-' + itemnumber).show();
                        $('.name-container-' + itemnumber).show();

                        $("#note-item-" + itemnumber).html(data.productQtyHtml);
                        $("#id-items" + itemnumber + "qty").val(data.productMinQty);

                        $('#product-options-wrapper-' + itemnumber).parent().attr('id', data.type);
                        $('.product-type').val(data.type);
                        $('.parent-class-id').val("product-options-wrapper-"+itemnumber);
                        $('.item-id').val(itemnumber);
                        
                        /* Configurable Options */
                        if (typeof (data.options.configurable_options) !== 'undefined') {
                            $('#product-options-wrapper-' + itemnumber).html(data.options.configurable_options).trigger('contentUpdated');
                            $('#product-options-wrapper-' + itemnumber).find('select.options').each(function () {
                                cnfname = $(this).attr('name');
                                updatedCnfName = 'items[' + itemnumber + ']' + cnfname;
                                $(this).attr('name', updatedCnfName);
                            });
                        }

                        /* Bundle Product Options */
                        if (typeof (data.options.bundle_options) !== 'undefined' && data.options.bundle_options !== '') {
                            $('#product-options-wrapper-' + itemnumber).html(data.options.bundle_options).trigger('contentUpdated');
                            $('#product-options-wrapper-' + itemnumber).find('.option .control').each(function () {
                                
                                inputType = $(this).find('.product.bundle.option').attr('type');
                                qtyname = $(this).find('.qty input.qty').attr('name');
                                validate = $(this).find('.product.bundle.option').data('validate');
                                selector = $(this).find('.product.bundle.option').attr('data-selector');
                                $(this).find('.qty input.qty').removeAttr("disabled");
                                if (typeof ($(this).find('.product.bundle.option').attr('name')) !== 'undefined' && $(this).find('.product.bundle.option').attr('name') !== '') {
                                    name = $(this).find('.product.bundle.option').attr('name');
                                    if (typeof (name) !== "undefined") {
                                        replacedName = name.replace('bundle_option', '');
                                    }
                                }

                                if (typeof (qtyname) !== 'undefined') {
                                    replacedQtyName = qtyname.replace('bundle_option_qty', '');
                                }

                                id = $(this).find('.product.bundle.option').attr('id');
                                qtyId = $(this).find('.qty input.qty').attr('id');
                                updatedId = 'items_' + itemnumber + '_' + id;
                                updatedQtyId = 'items_' + itemnumber + '_' + qtyId;

                                if (typeof (selector) !== 'undefined') {
                                    updatedSelector = selector.replace('bundle_option', 'items[' + itemnumber + '][bundle_option]');
                                    $(this).find('.product.bundle.option').attr('data-selector', updatedSelector);
                                }

                                if (typeof (validate) !== 'undefined') {
                                    updateValidate = validate.replace('bundle_option', 'items[' + itemnumber + '][bundle_option]');
                                    $(this).find('.product.bundle.option').attr('data-validate', updateValidate);
                                }

                                if (typeof (name) !== "undefined") {
                                    var updatedName = 'items[' + itemnumber + '][bundle_option]' + replacedName;
                                    $(this).find('.product.bundle.option').attr('name', updatedName);
                                }

                                if (typeof (replacedQtyName) !== 'undefined') {
                                    updatedQtyName = 'items[' + itemnumber + '][bundle_option_qty]' + replacedQtyName;
                                    $(this).find('.qty input.qty').attr('name', updatedQtyName);

                                }

                                if (typeof (updatedId) !== 'undefined') {
                                    $(this).find('.product.bundle.option').attr('id', updatedId);
                                }

                                if (typeof (updatedQtyId) !== 'undefined') {
                                    $(this).find('.qty input.qty').attr('id', updatedQtyId);
                                }

                                // Update Qty
                                $(this).find('.qty input.qty').attr('value', data.productMinQty);
                            });
                        }

                        /* Group Product Options */
                        if (typeof (data.options.group_options) !== 'undefined') {
                            $('#product-options-wrapper-' + itemnumber).html(data.options.group_options).trigger('contentUpdated');
                            $('#product-options-wrapper-' + itemnumber).find('#super-product-table tbody').each(function () {
                                $(data.options.group_options).find('.qty input.qty').each(function (everyQtyText) {
                                    var qtyTextboxName = $(this).attr("name");
                                    if (typeof (qtyTextboxName) !== 'undefined') {
                                        var removeSuperGroupFromQtyTextbox = qtyTextboxName.replace('super_group', '');
                                    }
                                    if (typeof (removeSuperGroupFromQtyTextbox) !== 'undefined') {
                                        var newQtyTextboxName = 'items[' + itemnumber + '][super_group]' + removeSuperGroupFromQtyTextbox.toString();
                                        var elementId = "#" + this.id;
                                        $(elementId).attr("name", newQtyTextboxName);
                                    }
                                });
                            });
                        }

                        /* Downloaded Product Options */
                        if (typeof (data.options.downloadable_options) !== 'undefined') {
                            $('#product-options-wrapper-' + itemnumber).html(data.options.downloadable_options).trigger('contentUpdated');
                            $('#product-options-wrapper-' + itemnumber).find('#downloadable-links-list .field').each(function () {
                                name = $(this).find('input').attr('name');
                                if (typeof (name) !== 'undefined') {
                                    updatedName = 'items[' + itemnumber + '][links]' + name.replace('links', '');
                                    $(this).find('input').attr('name', updatedName);
                                }
                            });
                        }

                        /* Manage Custom Options into all data types - Verified */
                        if (typeof (data.options.custom_options) !== 'undefined') {
                            var productType = data.type;

                            if (productType !== 'bundle'){
                                $('#product-options-wrapper-' + itemnumber).append(data.options.custom_options).trigger('contentUpdated');
                                /* Manage Multiple Element As Custom Options */
                                $('#product-options-wrapper-' + itemnumber).find('.control select, .control input, .control textarea').each(function () {

                                    var alreadyUpdatedFlag = false;
                                    var name = $(this).attr("name");
                                    if (name != undefined && name.indexOf('items') != -1){
                                        alreadyUpdatedFlag = true;
                                    }

                                    if (!alreadyUpdatedFlag){
                                        var id = $(this).attr("id");
                                        var inputType = $(this).attr("type");
                                        if (name === undefined || name === null) {
                                            updatedName = 'items[' + itemnumber + '][options]';
                                        } else if (name.indexOf('options') != -1 && inputType != "file"  && inputType != "hidden") {
                                            updatedName = 'items[' + itemnumber + '][options]' + name.replace('options', '');
                                        } else if (inputType == "file") {
                                            updatedName = name;
                                        } else {
                                            updatedName = 'items[' + itemnumber + '][' + name + ']';
                                        }
                                        $(this).attr('name', updatedName);

                                        if(typeof id !== 'undefined') {
                                            updatedId = 'items_' + itemnumber + '_' + id;
                                            $(this).attr('id', updatedId);
                                            $(this).attr('for', updatedId);
                                        }
                                    }
                                });
                            }
                        }

                        /* Gift Product Options */
                        if (typeof (data.options.giftcard_options) !== 'undefined') {
                            $('#product-options-wrapper-' + itemnumber).html(data.options.giftcard_options).trigger('contentUpdated');
                            $('#product-options-wrapper-' + itemnumber).find('.input-text').each(function () {
                                name = $(this).attr('name');
                                id = $(this).attr('id');
                                updatedId = 'items_' + itemnumber + '_' + id;
                                updatedName = 'items[' + itemnumber + '][' + name + ']';
                                $(this).attr('name', updatedName);
                                $(this).attr('id', updatedId);
                            });
                        }

                        $('.product-item-' + itemnumber + ' .img-right').append('<div class="option-message"><span style="color: #e02b27; font-size: 1.2rem;" class="option-need-item">' + data.messege + '</span></div>');

                        $('#product-options-wrapper-' + itemnumber).attr('data-itemnumber', itemnumber);
                        $('#product-options-wrapper-' + itemnumber).show();

                        var samples = '';
                        if (data.type == "downloadable" && typeof (data.options.downloadable_samples) !== 'undefined') {
                            samples += '<div class="downloadable_samples">';
                            samples += data.options.downloadable_samples;
                            samples += '</div>';
                        }
                        var className = "itemRenderAfterAjax" + itemnumber;
                        $('.product-item-' + itemnumber).html('<div class=' + className + ' data-role="content"><div data-role="collapsible"><div data-role="title" class="filter-options-title product-clicktitle">View Details</div><div data-role="content" class="filter-options-content-obs" style="display: none"><div class="img-left"><img style="width: 220px;" src=' + data.productimage + ' alt=""/></div><div class="img-right product-info-main"><h1 class="page-title"><span data-ui-id="page-title-wrapper" class="base"> <a href=' + data.product_url + '>' + data.name + '</a></span></h1><div class="product-info-price"><div class="price-box price-final_price"><span class="price-container price-final_price" data-role="priceBox" data-product-id=' + data.product_id + '><span class="price">' + data.price + '</span></span></div><div class="product-info-stock-sku"><div title="Availability" class="stock available"><span>' + data.is_in_stock + '</span></div><div class="product attribute sku"><strong class="type">SKU</strong><div itemprop="sku" class="value">' + data.sku + '</div></div></div></div>' + samples + '<div class="shortdescription">' + shortdescription + '</div></div><div>' + data.attributes + '</div></div></div></div>');
                    } else {
                        $('.product-item-' + itemnumber).html('<span style="color: #e02b27; font-size: 1.2rem;" class="option-need-item">Please enter valid SKU key.</span>');
                        $('#id-items' + itemnumber + 'sku-error').remove();
                        $('.product-sku-details.product-item-' + itemnumber).css("margin-bottom", "5px");
                        if ($('#product-thumbnail-' + itemnumber + ' img').attr('src')) {
                            $('#product-thumbnail-' + itemnumber + ' img').attr('src', '');
                        }
                    }

                    $('.product-thumbnail').show();
                    $('#product-thumbnail-' + itemnumber + ' img').attr('src', data.thumbnail);

                    /* Remove MoreInformation tab from view more options. */
                    $("#tab-label-two").empty();

                    $(".itemRenderAfterAjax" + itemnumber).on("click", function () {
                        var content = $(this).children().children().last();
                        var displayStatus = content.css('display');
                        $('.filter-options-content-obs').css("display", "none");
                        if (displayStatus == "none") {
                            content.css("display", "block");
                        } else {
                            content.css("display", "none");
                        }
                    });
                }
            });
        }

        $('.skus .action.reset').on('click', function () {
            $('#customer_sku_csv').val('');
            $('#uploadFile').val('');
            $('.orderbyskupage .item-sku-mapping').empty();
            $('.orderbyskupage .csv-error-skus').empty();
            $('.orderbyskupage .note-massege').empty();
        });

        $('.action.mappingskus').on('click', function () {
            var sku_file_exits = $('#customer_sku_csv').val();
            $('.csv-error-skus').empty();
            $('.item-sku-mapping').empty();
            if (sku_file_exits) {
                var fd = new FormData();
                fd.append('file', $('#customer_sku_csv')[0].files[0]);
                var checkUrl = urlBuilder.build('orderbysku/customer/skudata');
                var options = false;
                $.ajax({
                    showLoader: true,
                    url: checkUrl,
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: function (data) {
                        var optionUrl = data.optionurl;
                        if (data.error === true) {
                            $('#customer_sku_csv-error').remove();
                            $('.note-massege').html('<div for="customer_sku_csv" generated="true" class="mage-error" id="customer_sku_csv-error" style="display: block;">' + data.message + '</div>');
                        } else {
                            $('.note-massege').html('');
                            $('.item-sku-mapping').append('<div class="itemgrid"><table class="itemtable"><tbody><tr><th class="item-image">Image</th><th class="item-name">Name</th><th class="item-sku">Sku</th><th class="item-qty">Qty</th><th class="item-massege">Message</th></tr></tbody></table></div>');
                            $.each(data.maindata, function (key, value) {
                                if (value.invalidlist.length !== 0) {
                                    $.each(value.invalidlist, function (invalid, invalidvalue) {
                                        $('.item-sku-mapping').append('<div class="itemgrid invalid-data-sku" data-role="collapsible"><div data-role="title" class="itemgrid-title "><table class="itemtable"><tbody><tr><td colspan="5" class="item-sku">' + value.massege + ' : ' + invalidvalue + '</td></tr></tbody></table></div>');
                                    });
                                } else {
                                    var productUrl = value.productUrl;
                                    configure = '<a data-key="' + key + '" data-sku="' + value.sku + '" data-option-url="' + optionUrl + '?sku=' + value.sku + '&qty=' + value.qty + '" class="options_selection" id="options_selection" href="' + productUrl + '">Configure</a>';
                                    if (value.productwithOptionslist.length === 0) {
                                        options = false;
                                        configure = '';
                                    }
                                    $('.item-sku-mapping').append('<div class="itemgrid" id="' + value.typeid + '" data-role="collapsible"><div data-role="title" class="itemgrid-title "><table class="itemtable"><tbody><tr><td class="item-image"><img style="width: 50px;" src=' + value.itemimage + ' alt=""/></td><td class="item-name">' + value.name + '</td><td class="item-sku"><input type="text" readonly name="items[' + key + '][sku]" value="' + value.sku + '" /></td><td class="item-qty"><input type="text" name="items[' + key + '][qty]" value="' + value.qty + '" /></td><td class="item-massege">' + value.massege + ' ' + configure + '<div data-key="' + key + '" id="product-options-wrapper-csv-' + key + '" class="product-options"></div></td></tr></tbody></table></div></div>');
                                }
                            });

                            $('.item-sku-mapping').append('<div class="actions-toolbar"><div class="primary"><button class="action tocart primary" title="Add to Cart" type="submit"><span>Add to Cart</span></button></div></div>');
                            if (data.outstocklist.length !== 0) {
                                $('.csv-error-skus').append('<div class="out-of-stocklist" style="color: #e02b27; font-size: 1.2rem;">These sku are out of stock: </div>');
                                $.each(data.outstocklist, function (key, value) {
                                    $('.csv-error-skus .out-of-stocklist').append(value + ';');
                                });
                            }
                            if (data.qtylist.length !== 0) {
                                $('.csv-error-skus').append('<div class="qty-list" style="color: #e02b27; font-size: 1.2rem;">These sku\'s quantity not available: </div>');
                                $.each(data.qtylist, function (key, value) {
                                    $('.csv-error-skus .qty-list').append(value + ';');
                                });
                            }
                            if (data.nonsimplelist.length !== 0) {
                                $('.csv-error-skus').append('<div class="non-simple-list" style="color: #e02b27; font-size: 1.2rem;">These sku\'s are need to choose options: </div>');
                                $.each(data.nonsimplelist, function (key, value) {
                                    $('.csv-error-skus .non-simple-list').append(value + ';');
                                });
                            }
                            if (data.invalidlist.length !== 0) {
                                $('.csv-error-skus').append('<div class="invalid-list" style="color: #e02b27; font-size: 1.2rem;">These sku are Invalid: </div>');
                                $.each(data.invalidlist, function (key, value) {
                                    $('.csv-error-skus .invalid-list').append(value + ';');
                                });
                            }
                            var temp = $('.item-sku-mapping').wrap('<p/>').parent().html();
                            $('.item-sku-mapping').unwrap();
                            $(".item-sku-mapping").remove();
                            $('.orderbyskupage .box-upload').append(temp);


                            $('#product_addtocart_form_2').find('.options_selection').each(function () {
                                this.click();
                            });
                        }
                    },
                    error: function (error) {
                        //alert(error);
                    }
                });
            } else {
                alert("Please upload csv file");
            }
        });

        $(document).on('click', '#options_selection', function (e) {
            e.preventDefault();
            var name;
            var updatedName;
            var cnfname;
            var updatedCnfName;
            var id;
            var updatedId;
            var replacedName;
            var updatedQtyName;
            var optionCheckUrl = $(this).attr('data-option-url');
            var itemnumber = $(this).attr('data-key');
            var options = false;
            var configure = '';
            $('#product-options-wrapper-csv-' + itemnumber).html('');
            $.ajax({
                showLoader: true,
                url: optionCheckUrl,
                processData: false,
                contentType: false,
                cache: false,
                type: 'POST',
                success: function (data) {
                    $('#product-options-wrapper-csv-' + itemnumber).addClass('orderbysku-pro-opt');
                    if (typeof (data.options.custom_options) !== 'undefined') {
                        $('#product-options-wrapper-csv-' + itemnumber).append(data.options.custom_options).trigger('contentUpdated');
                        $('#product-options-wrapper-csv-' + itemnumber).find('.control select, .control input, .control textarea').each(function () {
                            var name = $(this).attr("name");
                            var id = $(this).attr("id");
                            var inputType = $(this).attr("type");
                            if (name === undefined || name === null) {
                                updatedName = 'items[' + itemnumber + '][options]';
                            } else if (name.indexOf('options') != -1 && inputType != "file"  && inputType != "hidden") {
                                updatedName = 'items[' + itemnumber + '][options]' + name.replace('options', '');
                            } else if (inputType == "file") {
                                updatedName = name;
                            } else {
                                updatedName = 'items[' + itemnumber + '][' + name + ']';
                            }
                            $(this).attr('name', updatedName);

                            if(typeof id !== 'undefined') {
                                updatedId = 'items_' + itemnumber + '_' + id;
                                $(this).attr('id', updatedId);
                                $(this).attr('for', updatedId);
                            }
                        });
                    }

                    if (typeof (data.options.bundle_options) !== 'undefined') {
                        $('#product-options-wrapper-csv-' + itemnumber).append(data.options.bundle_options).trigger('contentUpdated');
                        $('#product-options-wrapper-csv-' + itemnumber).find('.option .control').each(function () {

                            $(this).find('.qty input.qty').removeAttr("disabled");
                            name = $(this).find('.product.bundle.option').attr('name');
                            qtyname = $(this).find('.qty input.qty').attr('name');
                            if (typeof (name) !== "undefined") {
                                replacedName = name.replace('bundle_option', '');
                            }
                            if (typeof (qtyname) !== 'undefined') {
                                replacedQtyName = qtyname.replace('bundle_option_qty', '');
                            }

                            id = $(this).find('.product.bundle.option').attr('id');
                            qtyId = $(this).find('.qty input.qty').attr('id');
                            updatedId = 'items_' + itemnumber + '_' + id;
                            updatedQtyId = 'items_' + itemnumber + '_' + qtyId;
                            validationString = $(this).find('.product.bundle.option').attr('data-validate');
                            selector = $(this).find('.product.bundle.option').attr('data-selector');

                            if (typeof (validationString) !== 'undefined') {
                                updateValidate = validationString.replace('bundle_option', 'items[' + itemnumber + '][bundle_option]');
                                $(this).find('.product.bundle.option').attr('data-validate', updateValidate);
                            }

                            if (typeof (name) !== "undefined") {
                                updatedName = 'items[' + itemnumber + '][bundle_option]' + replacedName;
                                updatedValidation = $(this).find('.product.bundle.option').attr('name');
                                $(this).find('.product.bundle.option').attr('name', updatedName);
                            }

                            if (typeof (replacedQtyName) !== 'undefined') {
                                updatedQtyName = 'items[' + itemnumber + '][bundle_option_qty]' + replacedQtyName;
                                $(this).find('.qty input.qty').attr('name', updatedQtyName);
                            }

                            if (typeof (updatedId) !== 'undefined') {
                                $(this).find('.product.bundle.option').attr('id', updatedId);
                            }

                            if (typeof (updatedQtyId) !== 'undefined') {
                                $(this).find('.qty input.qty').attr('id', updatedQtyId);
                            }

                            if (typeof (selector) !== 'undefined') {
                                updatedSelector = selector.replace('bundle_option', 'items[' + itemnumber + '][bundle_option]');
                                $(this).find('.product.bundle.option').attr('data-selector', updatedSelector);
                            }
                        });
                    }

                    if (typeof (data.options.configurable_options) !== 'undefined') {
                        $('#product-options-wrapper-csv-' + itemnumber).append(data.options.configurable_options).trigger('contentUpdated');
                        $('#product-options-wrapper-csv-' + itemnumber).find('select.options').each(function () {
                            cnfname = $(this).attr('name');
                            updatedCnfName = 'items[' + itemnumber + ']' + cnfname;
                            $(this).attr('name', updatedCnfName);
                        });
                    }

                    if (typeof (data.options.downloadable_options) !== 'undefined') {
                        $('#product-options-wrapper-csv-' + itemnumber).append(data.options.downloadable_options).trigger('contentUpdated');
                        $('#product-options-wrapper-csv-' + itemnumber).find('#downloadable-links-list .field').each(function () {
                            name = $(this).find('input').attr('name');
                            if (typeof (name) !== 'undefined') {
                                updatedName = 'items[' + itemnumber + '][links]' + name.replace('links', '');
                                $(this).find('input').attr('name', updatedName);
                            }
                        });
                    }

                    if (typeof (data.options.group_options) !== 'undefined') {
                        $('#product-options-wrapper-csv-' + itemnumber).append(data.options.group_options).trigger('contentUpdated');
                        $('#product-options-wrapper-csv-' + itemnumber).find('#super-product-table tbody').each(function () {

                            $(data.options.group_options).find('.qty input.qty').each(function () {
                                var qtyTextboxName = $(this).attr("name");
                                if (typeof (qtyTextboxName) !== 'undefined') {
                                    var removeSuperGroupFromQtyTextbox = qtyTextboxName.replace('super_group', '');
                                }
                                if (typeof (removeSuperGroupFromQtyTextbox) !== 'undefined') {
                                    var newQtyTextboxName = 'items[' + itemnumber + '][super_group]' + removeSuperGroupFromQtyTextbox.toString();
                                    var elementId = "#" + this.id;
                                    $(elementId).attr("name", newQtyTextboxName);
                                }
                            });
                        });
                    }

                    /* Gift Product Options */
                    if (typeof (data.options.giftcard_options) !== 'undefined') {
                        $('#product-options-wrapper-csv-' + itemnumber).html(data.options.giftcard_options).trigger('contentUpdated');
                        $('#product-options-wrapper-csv-' + itemnumber).find('.input-text').each(function () {
                            name = $(this).attr('name');
                            id = $(this).attr('id');
                            updatedId = 'items_' + itemnumber + '_' + id;
                            updatedName = 'items[' + itemnumber + '][' + name + ']';
                            $(this).attr('name', updatedName);
                            $(this).attr('id', updatedId);
                        });
                    }
                }
            });
        });

        $("#orderbyskublock").on("change", "select", function () {
            var input = $(this);
            // Validate date and time select box into dropdown.
            var dataRole = input.attr('data-role');
            if (dataRole == "calendar-dropdown") {
                var allCalendarDropdown = true;
                var fieldsetId = $(this).closest("fieldset").attr('id');
                $('#' + fieldsetId + ' option:selected').each(function () {
                    if (allCalendarDropdown && this.value == ""){
                        allCalendarDropdown = false;
                    }
                });
                if (!allCalendarDropdown){
                    return false;
                }
                $('#'+fieldsetId).attr('data-calendar-dropdown','1');
            }

            // If product belong from simple with custom options.
            var productType = $(this).closest("div.additional").find("input[name='product-type']").val();
            var parentId = $(this).closest("div.additional").find("input[name='parent-class-id']").val();
            var itemId = $(this).closest("div.additional").find("input[name='item-id']").val();
            calculateProductPrice(input, productType, parentId, itemId);
        });

        $("#orderbyskublock").on('click', 'input[type=radio]', function () {
            var input = $(this);
            var productType = $(this).closest("div.additional").find("input[name='product-type']").val();
            var parentId = $(this).closest("div.additional").find("input[name='parent-class-id']").val();
            var itemId = $(this).closest("div.additional").find("input[name='item-id']").val();
            calculateProductPrice(input, productType, parentId, itemId);
        });

        $("#orderbyskublock").on('change', '[type=checkbox]', function () {
            var input = $(this);
            var productType = $(this).closest("div.additional").find("input[name='product-type']").val();
            var parentId = $(this).closest("div.additional").find("input[name='parent-class-id']").val();
            var itemId = $(this).closest("div.additional").find("input[name='item-id']").val();
            calculateProductPrice(input, productType, parentId, itemId);
        });

        $("#orderbyskublock").on('change', '[type=text]', function () {
            var input = $(this);
            var validateInput = input.attr("data-sku");
            if (validateInput) {
                return true;
            }
            var productType = $(this).closest("div.additional").find("input[name='product-type']").val();
            var parentId = $(this).closest("div.additional").find("input[name='parent-class-id']").val();
            var itemId = $(this).closest("div.additional").find("input[name='item-id']").val();
            calculateProductPrice(input, productType, parentId, itemId);
        });

        $("#orderbyskublock").on("change", "textarea", function () {
            var input = $(this);
            var productType = $(this).closest("div.additional").find("input[name='product-type']").val();
            var parentId = $(this).closest("div.additional").find("input[name='parent-class-id']").val();
            var itemId = $(this).closest("div.additional").find("input[name='item-id']").val();
            calculateProductPrice(input, productType, parentId, itemId);
        });


        function calculateProductPrice(input, productType, parentId, itemId) {
            switch (productType) {
                case "configurable":
                    configurableProduct(input, parentId, itemId);
                    break;
                default:
                    calculateProductPriceWithCustomOptions(input, parentId, itemId);
                    break;
            }
        }

        function configurableProduct(input, parentId, itemId) {
            // Below code is used to get super attribute ids
            var superAttributeKey = [];
            $('#' + parentId + ' select').each(function () {
                var inputData = $(this);
                var superAttributeFullName = inputData.attr('name');
                if(superAttributeFullName.indexOf('super_attribute') != -1){
                    var replaceString = "items[" + itemId + "][super_attribute]";
                    var finalString = superAttributeFullName.replace(replaceString, "").replace("[", "").replace("]", "");
                    superAttributeKey.push(finalString);
                }
            });

            // Below code is used to get selected options ids
            var configureProductId = '0';
            var superAttributeValue = $('#' + parentId + ' :selected').map(function () { // ["city1","c2","s1","s5"]
                if (this.getAttribute("data-product-id")){
                    configureProductId = this.getAttribute("data-product-id");
                    return $(this).val();
                }
            });

            var supperAttribute = {};
            for (var i = 0; i < superAttributeKey.length; i++) {
                supperAttribute[superAttributeKey[i]] = superAttributeValue[i];
            }

            var customOptionsPrice = 0;

            /* Get Price From Checked Checkbox */
            $('#' + parentId + ' :input.product-custom-option[type=checkbox]:checked').each(function () {
                customOptionsPrice += parseInt(this.getAttribute("price"));
            });

            $('#' + parentId + ' :input[type=radio]').each(function () {
                if ($(this).prop('checked')) {
                    customOptionsPrice += parseInt(this.getAttribute("price"));
                }
            });

            /* Get Selected Options Price From Select except date options */
            $('#' + parentId + ' option:selected').each(function () {
                var calendarDropdown = $(this).parent().attr('data-role');
                if (!this.getAttribute("data-product-id") && calendarDropdown !== "calendar-dropdown" && this.getAttribute("price")) {
                    customOptionsPrice += parseInt(this.getAttribute("price"));
                }
            });

            /* Get All Textbox values */
            $('#' + parentId + ' :input[type=text]').each(function () {
                var inputTextbox = $(this);
                if (inputTextbox.val()) {
                    customOptionsPrice += parseInt(this.getAttribute("data-price-amount"));
                }
            });

            /* Get All Textarea values */
            $('#' + parentId + ' textarea').each(function () {
                var inputTextarea = $(this);
                if (inputTextarea.val()) {
                    customOptionsPrice += parseInt(this.getAttribute("data-price-amount"));
                }
            });

            /* Get All file values */
            $('#' + parentId + ' :input[type=file]').each(function(index, field) {
                var file = field.files[0];
                if (file && file.size > 0){
                    customOptionsPrice += parseInt(this.getAttribute("data-price-amount"));
                }
            });

            $('#' + parentId).find('.fieldset-product-options-inner').each(function () {
                if (this.getAttribute('data-calendar-dropdown')){
                    var calenderDropdown = this.getAttribute('data-calendar-dropdown');
                    if (calenderDropdown == '1'){
                        customOptionsPrice += parseInt(this.getAttribute("data-price-amount"));
                    }
                }
            });

            // Call Ajax
            $.ajax({
                showLoader: true,
                url: urlBuilder.build('orderbysku/customer/configureProductPrice'),
                data: {
                    configureProductId: configureProductId,
                    supperAttribute: supperAttribute,
                    customOptionsPrice: customOptionsPrice
                },
                type: 'POST',
                success: function (data) {
                    $("#product-thumbnail-" + itemId).html(data.image);
                    $('.product-price-' + itemId).html(data.html);
                }
            });
        }

        /**
         *
         * @param input
         * @param parentId
         * @param itemId
         */
        function calculateProductPriceWithCustomOptions(input, parentId, itemId) {

            var customOptionsPrice = 0;

            /* Get Price From Checked Checkbox */
            $('#' + parentId + ' :input.product-custom-option[type=checkbox]:checked').each(function () {
                customOptionsPrice += parseInt(this.getAttribute("price"));
            });

            $('#' + parentId + ' :input[type=radio]').each(function () {
                if ($(this).prop('checked')) {
                    customOptionsPrice += parseInt(this.getAttribute("price"));
                }
            });

            /* Get Selected Options Price From Select except date options */
            $('#' + parentId + ' option:selected').each(function () {
                var calendarDropdown = $(this).parent().attr('data-role');
                if (calendarDropdown !== "calendar-dropdown" && this.getAttribute("price")) {
                    customOptionsPrice += parseInt(this.getAttribute("price"));
                }
            });

            /* Get All Textbox values */
            $('#' + parentId + ' :input[type=text]').each(function () {
                var inputTextbox = $(this);
                if (inputTextbox.val()) {
                    customOptionsPrice += parseInt(this.getAttribute("data-price-amount"));
                }
            });

            /* Get All Textarea values */
            $('#' + parentId + ' textarea').each(function () {
                var inputTextarea = $(this);
                if (inputTextarea.val()) {
                    customOptionsPrice += parseInt(this.getAttribute("data-price-amount"));
                }
            });

            /* Get All file values */
            $('#' + parentId + ' :input[type=file]').each(function(index, field) {
                var file = field.files[0];
                if (file && file.size > 0){
                    customOptionsPrice += parseInt(this.getAttribute("data-price-amount"));
                }
            });

            var mainProductId = 0;
            $('#' + parentId + ' input[type=hidden]').each(function () {
                if (this.name == "customOptionProductId") {
                    mainProductId = this.value;
                }
            });


            $('#' + parentId).find('.fieldset-product-options-inner').each(function () {
                if (this.getAttribute('data-calendar-dropdown')){
                    var calenderDropdown = this.getAttribute('data-calendar-dropdown');
                    if (calenderDropdown == '1'){
                        customOptionsPrice += parseInt(this.getAttribute("data-price-amount"));
                    }
                }
            });

            $.ajax({
                showLoader: true,
                url: urlBuilder.build('orderbysku/customer/productCustomOptionPrice'),
                data: {customOptionProductPrice: customOptionsPrice, mainProductId: mainProductId},
                type: 'POST',
                success: function (data) {
                    $('.product-price-' + itemId).html(data.html);
                }
            });
        }

        $.validator.addMethod(
            'validate-custom-file-csv', function (value) {
                if (value) {
                    if (value.split('.').pop() === 'csv') {
                        return true;
                    } else {
                        $('.orderbyskupage .note-massege').empty();
                        return false;
                    }
                } else {
                    return true;
                }
            }, $.mage.__('Please enter valid CSV file format.')
        );
    });

    $("#customer_sku_csv").change(function () {
        $("#uploadFile").val($(this).val());
    });
});