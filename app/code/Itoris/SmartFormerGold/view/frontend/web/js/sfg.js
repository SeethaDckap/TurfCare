SFG = function (config) { this.init(config); }
SFG.prototype = {
    validators: [],
    callback: {
        afterLoad: function() {},
        beforeSubmit: function(btn) { return true; }
    },
    init: function(config) {
        var _this = this;
        if (!window.jQuery) {
            setTimeout(function(){_this.init(config)}, 200);
            return;
        }
        this.config = config;
        this.formId = config.form_id;
        this.container = jQuery('#sfg_fieldset'+this.formId);
        this.container[0].sfgObject = this;
        this.form = this.container.closest('form');
        this.form.on('submit', function(){
            if (_this.container.find('.sfg-error').length > 0) return false;
        });
        this.getAllElements().each(function(index, element){
            jQuery(element).on('blur', function(){_this.validate(this)});
        });
        jQuery(document).on('mousedown', function(){
            jQuery('.sfg-hint').each(function(index, hint){
               jQuery(hint).remove();
            });
        });
        this.screenFiles();
        this.attachCalendar();
        if (config.autoresponsive) this.autoResponsive.init(this);
        this.enableDepencies();
        this.canvas.initAll(this);
        jQuery(document).ready(function(){
            _this.callback.afterLoad();
        });        
    },
    getAllElements: function(){
        return jQuery('#sfg_fieldset'+this.formId+' > *');
    },
    validate: function(field) {
        jQuery(field).removeClass('sfg-error');
        if (jQuery(field).hasClass('sfg-hidden')) return;
        var msg = '', validator = field.getAttribute('validation'), sfg_name = field.getAttribute('sfg_name');
        if (!sfg_name) sfg_name = field.name;
        if (field.getAttribute('sfg_required') == '1') {
            if (jQuery(field).is('canvas')) {
                if (!field.touched) msg = this.validators['Required'](document.createElement('input'), sfg_name);
            } else {
                if (!jQuery(field).is(':file') || jQuery(field).is(':visible')) msg = this.validators['Required'](field, sfg_name);
            }
        }
        if (!msg && field.getAttribute('sfg_group_required') == '1') {
            msg = this.validators['Group Required'](field, sfg_name);
            if (msg) jQuery('input[name="'+field.name+'"]').each(function(index, _field){jQuery(_field).addClass('sfg-error');});
        }
        if (!msg && field.getAttribute('sfg_equal_to')) msg = this.validators['Check identical'](field, sfg_name, field.getAttribute('sfg_equal_to'));
        if (!msg && validator) msg = this.validators[validator](field, sfg_name);
        if (msg) jQuery(field).addClass('sfg-error');
        this.hint(field, msg);
        return msg;
    },
    validateAll: function() {
        var _this = this, focused = false;
        this.getAllElements().each(function(index, el){
            if (_this.validate(el) && !focused) {
                el.focus();
                focused = true;
            }
        });
        return this.container.find('.sfg-error').length == 0;
    },
    hint: function(field, msg) {
        if (field.sfg_hint) field.sfg_hint.remove();
        if (msg) {
            field.sfg_hint = jQuery('<div class="sfg-hint"></div>');
            field.sfg_hint[0]._parent = field;
            field.sfg_hint.css({left: jQuery(field).position().left - 10 + 'px', top: jQuery(field).position().top + field.offsetHeight + 'px'});
            field.sfg_hint.html(msg);
            field.sfg_hint.insertAfter(field);
        }
    },
    getPdf: function(btn) {
        var isFormValid = this.validateAll();
        if (isFormValid) {
            if (this.callback.beforeSubmit(btn)) {
                this.form.append('<input type="hidden" name="sfg_submitter" id="sfg_submitter" value="'+btn.getAttribute('sfg-element-id')+'" />');
                this.form.append('<input type="hidden" name="sfg_pdf" id="sfg_pdf" value="1" />');
                this.canvas.beforeSubmit();
                this.form.action = this.config.submit_url;
                this.form.submit();
                this.form.find('#sfg_submitter').remove();
                this.form.find('#sfg_pdf').remove();
            }
        }
        return false;
    },
    submit: function(btn) {
        btn.disabled = true;
        var isFormValid = this.validateAll();
        if (!isFormValid) {
            btn.disabled = false;
        } else {
            if (this.callback.beforeSubmit(btn)) {
                this.form.append('<input type="hidden" name="sfg_submitter" value="'+btn.getAttribute('sfg-element-id')+'" />');
                this.canvas.beforeSubmit();
                if (this.config.submit_ajax) {
                    return this.submitAjax();
                } else {
                    this.form.submit();
                }
            } else {
                btn.disabled = false;
                return false;
            }
        }
        return isFormValid;
    },
    submitSimple: function(btn) {
        btn.disabled = true;
        this.container.find('.sfg-error').each(function(index, el){
            jQuery(el).removeClass('sfg-error');
        });
        if (this.callback.beforeSubmit(btn)) {
            this.form.append('<input type="hidden" name="sfg_submitter" value="'+btn.getAttribute('sfg-element-id')+'" />');
            this.canvas.beforeSubmit();
            if (this.config.submit_ajax) {
                return this.submitAjax();
            } else {
                this.form.submit();
            }
        } else {
            btn.disabled = false;
            return false;
        }
        return true;
    },
    submitAjax: function(){
        var _this = this;
        var formData = new FormData(this.form[0]);
        var url = this.form.attr("action");
        if (!url) url = this.config.submit_url;
        url += (url.indexOf('?') > -1 ? '&' : '?') + 'isAjax=1';
        this.container.addClass('sfg-loader');
        jQuery.ajax({
            url: url,
            type: 'POST',
            data: formData,
            async: true,
            success: function (data) {
                var placeholder = jQuery('<div>').insertBefore(_this.form);
                placeholder[0].scrollIntoView();
                _this.form.remove();
                placeholder.after(data);
                placeholder.remove();
                _this.callback.afterLoad();
                //alert(jQuery(data).find('.sfg-messages').text());
            },
            cache: false,
            contentType: false,
            processData: false
        });       
        return false;
    },
    autoResponsive: {
        mobileWidth: 480,
        init: function(sfgObject){
            this.sfgObject = sfgObject;
            var _this = this;
            this.sfgObject.container[0].sfgInitialSize = {width: this.sfgObject.container[0].offsetWidth, height: this.sfgObject.container[0].offsetHeight};
            this.resortElements();
            this.resize();
            this.sfgObject.container.css({width: 'auto'});
            this.sfgObject.container.addClass('sfg-form-responsive');
            jQuery(window).on('resize', function(){_this.resize();});
        },
        resortElements: function(){
            var objects = this.sfgObject.getAllElements(), objects2 = [], newOrder = [];

            for(var i=0; i<objects.length - 1; i++) {
                objects[i].initialOrder = i;
                for(var o=i+1; o<objects.length; o++) {
                    if (Math.abs(parseInt(objects[i].style.top) - parseInt(objects[o].style.top)) < 10) objects[o].style.top = objects[i].style.top; //assuming on the same line horizontally
                    if (Math.abs(parseInt(objects[i].style.left) - parseInt(objects[o].style.left)) < 10) objects[o].style.left = objects[i].style.left; //assuming on the same line vertically
                }
            }
            for(var i=0; i<objects.length; i++) {
                if (['input', 'select', 'textarea', 'canvas'].indexOf(objects[i].tagName.toLowerCase()) > -1) {
                    if (objects[i].type && ['checkbox', 'radio'].indexOf(objects[i].type.toLowerCase()) > -1) continue;
                    objects[i].topRelations = []; objects[i].bottomRelations = [];
                    for(var o=0; o<objects.length; o++) {
                        if (!objects[o].sfgRelated && ['span', 'div'].indexOf(objects[o].tagName.toLowerCase()) > -1) {
                            var deltaX = Math.abs(parseInt(objects[i].style.left) - parseInt(objects[o].style.left));
                            var deltaY = Math.abs(parseInt(objects[i].style.top) - parseInt(objects[o].style.top) - objects[o].offsetHeight);
                            if (deltaX < 20 && deltaY < 12) {objects[o].sfgRelated = true; objects[i].topRelations.push(objects[o]);}
                        }
                    }
                    for(var o=0; o<objects.length; o++) {
                        if (!objects[o].sfgRelated && ['span', 'div'].indexOf(objects[o].tagName.toLowerCase()) > -1) {
                            var deltaX = Math.abs(parseInt(objects[i].style.left) - parseInt(objects[o].style.left));
                            var deltaY = Math.abs(parseInt(objects[i].style.top) + objects[i].offsetHeight - parseInt(objects[o].style.top));
                            if (deltaX < 20 && deltaY < 12) {objects[o].sfgRelated = true; objects[i].bottomRelations.push(objects[o]);}
                        }
                    }
                }
            }
            for(var i=0; i<objects.length - 1; i++) {
                for(var o=i+1; o<objects.length; o++) {
                    var top1 = parseInt(objects[i].style.top), top2 = parseInt(objects[o].style.top);
                    var left1 = parseInt(objects[i].style.left), left2  = parseInt(objects[o].style.left);
                    if (top1 > top2 || top1 == top2 && left1 > left2) {
                        tmp = objects[o]; objects[o] = objects[i]; objects[i] = tmp;
                    }
                }
            }
            
            for (var i=0; i<objects.length; i++) {
                this.sfgObject.container[0].appendChild(objects[i]);
                if (!objects[i].sfgInitialPos) {
                    objects[i].sfgInitialPos = {left: objects[i].style.left, top: objects[i].style.top, width: objects[i].style.width, height: objects[i].style.height, maxWidth: objects[i].style.maxWidth};
                    objects[i].style.left = parseInt(objects[i].style.left) / this.sfgObject.container[0].sfgInitialSize.width * 100 + '%';                    
                    if (objects[i].tagName.toLowerCase() == 'span' || objects[i].tagName.toLowerCase() == 'div') {
                        objects[i].style.maxWidth = objects[i].offsetWidth + 'px';
                        objects[i].style.width = 'auto';
                    } else {
                        objects[i].style.width = objects[i].offsetWidth / this.sfgObject.container[0].sfgInitialSize.width * 100 + '%';
                    }
                    objects[i].sfgResponsivePos = {left: objects[i].style.left, top: objects[i].style.top, width: objects[i].style.width, height: objects[i].style.height, maxWidth: objects[i].style.maxWidth};
                    objects[i].isResponsive = true;
                }
                newOrder[objects[i].initialOrder] = i;
            }
            
            for (var i=0; i<objects.length; i++) {
                if (objects[i].topRelations) {
                    for(var o=0; o<objects[i].topRelations.length; o++) {
                        jQuery(objects[i].topRelations[o]).insertBefore(objects[i]);
                    }
                }
                if (objects[i].bottomRelations) {
                    for(var o=0; o<objects[i].bottomRelations.length; o++) {
                        jQuery(objects[i].bottomRelations[o]).insertAfter(objects[i]);
                    }
                }
            }
            
        },
        resize: function(){
            this.sfgObject.container.removeClass('sfg-mobile');
            if (this.sfgObject.container[0].parentNode.offsetWidth < this.mobileWidth) this.sfgObject.container.addClass('sfg-mobile');
            var objects = this.sfgObject.getAllElements(), height = 0;
            for(var i=0; i<objects.length; i++) {
                if (this.sfgObject.container[0].offsetWidth >= this.sfgObject.container[0].sfgInitialSize.width && objects[i].isResponsive) {
                    objects[i].style.left = objects[i].sfgInitialPos.left;
                    objects[i].style.width = objects[i].sfgInitialPos.width;
                    //objects[i].style.height = objects[i].sfgInitialPos.height;
                    //objects[i].style.top = objects[i].sfgInitialPos.top;
                    objects[i].style.maxWidth = objects[i].sfgInitialPos.maxWidth;
                    objects[i].isResponsive = false;
                }
                if (this.sfgObject.container[0].offsetWidth < this.sfgObject.container[0].sfgInitialSize.width && !objects[i].isResponsive) {
                    objects[i].style.left = objects[i].sfgResponsivePos.left;
                    if ((!objects[i].type || ['button', 'submit', 'reset'].indexOf(objects[i].type.toLowerCase()) == -1) && objects[i].tagName.toLowerCase() != 'button') objects[i].style.width = objects[i].sfgResponsivePos.width;
                    //objects[i].style.height = objects[i].sfgResponsivePos.height;
                    //objects[i].style.top = objects[i].sfgResponsivePos.top;
                    objects[i].style.maxWidth = objects[i].sfgResponsivePos.maxWidth;
                    objects[i].isResponsive = true;
                }
                if (height < objects[i].offsetTop + objects[i].offsetHeight) height = objects[i].offsetTop + objects[i].offsetHeight;
            }
            this.sfgObject.container[0].style.height = height + 20 + 'px';            
        }
    },
    screenFiles: function(){
        var _this = this;
        this.container.find('input[type="file"]').each(function(index, file){
            var fileName = file.getAttribute('value');
            if (fileName && fileName !== null) {
                var fileLabel = jQuery('<div class="sfg-filename" style="position:absolute"></div>').insertBefore(file);
                fileLabel.css({left: file.style.left, top: file.style.top});
                var link = jQuery('<span class="sfg-file-link"></span>').appendTo(fileLabel);
                link.text(fileName);
                link.on('click', function(){document.location.href = _this.config.fileDownloadUrl + '?object=' + file.getAttribute('sfg-element-id');});
                jQuery(file).hide();
                var remove = jQuery('<span class="sfg-file-remove"></span>').appendTo(fileLabel);
                remove.on('click', function(){
                    jQuery(fileLabel).remove();
                    jQuery(file).show();
                    jQuery('<input type="hidden" name="'+file.name+'" value="" />').insertBefore(file)
                })
            } 
        });
    },
    attachCalendar: function(){
        var calendarElements = this.container.find('[sfg-calendar="1"]'), _this = this;
        calendarElements.each(function(index, btn){
           require(["jquery","mage/calendar","prototype"], function(){
               var inputName = jQuery(btn).attr('sfg-calendar-field');
               if (inputName) _this.container.find('[name="'+inputName+'"]').calendar({maxDate: "-1d", changeMonth: true, changeYear: true});
           });
        });
    },
    showCalendar: function(btn){
        var inputName = jQuery(btn).attr('sfg-calendar-field');
        if (inputName) this.container.find('[name="'+inputName+'"]').datepicker('show');
    },
    captchaReload: function(name) {
        var obj = this.container.find('[name="'+name+'"]')[0];
        if (!obj) return;
        var src = obj.src;
        if (src) {
            var pos = src.indexOf('&action=reload');
            if (pos > -1) src = src.substring(0, pos);
            src += '&action=reload&rnd='+Math.random().toString().replace('.','');;
            obj.src = src;
        }
    },
    enableDepencies: function(){
        var _this = this;
        jQuery('#sfg_fieldset'+this.formId+' > input, #sfg_fieldset'+this.formId+' > select, #sfg_fieldset'+this.formId+' > textarea').on('change', function(){
           _this.checkDependencies(this); 
        });
        this.checkDependencies();
    },
    checkDependencies: function(){
        var _this = this, repeat = false;
        this.container.find('[sfg-hidden-if]:not([sfg-hidden-if=""])').each(function(index, obj){
            var condition = jQuery(obj).attr('sfg-hidden-if');
            if (condition) {
                var result = false;
                //replacing variables
                jQuery(condition.match(/{{[^}]*}}/g)).each(function(index, match){
                    var el = _this.container.find('[name="'+match.replace('{{','').replace('}}','')+'"]');
                    var value = el[0] ? el.val() : '';
                    if (el[0] && el[0].type && ['checkbox', 'radio'].indexOf(el[0].type.toLowerCase()) > -1 && !el[0].checked) value = '';
                    if (value.join) value = value.join();
                    if (!isNaN(parseFloat(value)) && isFinite(value)) {} else value = '"'+value.replace(/^\s+|\s+$/g, '').replace(/\"/g, '\\"')+'"';
                    condition = condition.replace(match, value);
                });
                //alert(condition);
                try {
                    eval('result = (' + condition + ') ? true : false');
                } catch (e) { }
                if (result) {
                    if (_this.hideField(obj)) repeat = true;
                } else {
                    if (_this.showField(obj)) repeat = true;
                }
            }
        });
        if (repeat) this.checkDependencies(); else if (this.config.autoresponsive) this.autoResponsive.resize();
    },
    hideField: function(obj) {
        if (!jQuery(obj).hasClass('sfg-hidden')) {
            jQuery(obj).addClass('sfg-hidden');
            if (jQuery(obj).is("select")) obj.selectedIndex = 0;
                else if (jQuery(obj).is(":checkbox, :radio")) obj.checked = false;
                else if (!jQuery(obj).is(":button, :reset, :submit")) obj.value = '';
            if (this.config.autoresponsive) {
                var allowShift = true;
                obj.sfgHidden = {delta: 99999, elements: []}
                this.getAllElements().each(function(index, el){
                    if (!allowShift) return;
                    if (el.style.top == obj.style.top && !jQuery(el).hasClass('sfg-hidden')) allowShift = false;
                    var _delta = parseFloat(el.style.top) - parseFloat(obj.style.top);
                    if (_delta > 0) {
                        if (_delta < obj.sfgHidden.delta) obj.sfgHidden.delta = _delta; 
                        obj.sfgHidden.elements.push(el);
                    }
                });
                if (allowShift && obj.sfgHidden.delta && obj.sfgHidden.elements.length) {
                    jQuery(obj.sfgHidden.elements).each(function(index, el){
                        el.style.top = parseFloat(el.style.top) - obj.sfgHidden.delta + 'px';
                    });
                } else obj.sfgHidden = false;
            }
            return true;
        }
    },
    showField: function(obj) {
        if (jQuery(obj).hasClass('sfg-hidden')) {
            jQuery(obj).removeClass('sfg-hidden');
            if (obj.sfgHidden) {
                jQuery(obj.sfgHidden.elements).each(function(index, el){
                    el.style.top = parseFloat(el.style.top) + obj.sfgHidden.delta + 'px';
                });
                obj.sfgHidden = false;
            }
            return true;
        }
    },
    canvas: {
        initAll: function(sfgObject) {
            var _this = this;
            this.sfgObject = sfgObject;
            jQuery('#sfg_fieldset'+sfgObject.formId+' > canvas').each(function(index, canvas){ _this.init(canvas); });
        },
        beforeSubmit: function() {
            var _this = this;
            jQuery('#sfg_fieldset'+_this.sfgObject.formId+' > canvas').each(function(index, canvas){
                _this.sfgObject.form.append('<input type="hidden" name="'+jQuery(canvas).attr('name')+'" value="'+canvas.toDataURL()+'" />');
            });
        },
        init: function(canvas) {
            var context = canvas.getContext("2d"), _this = this;
            context.lineCap = 'round';
            context.lineJoin = 'round';
            canvas.setPenSize = function(size){context.lineWidth = size;}
            canvas.setPenColor = function(color){context.strokeStyle = color;}
            canvas.setBGColor = function(color){
                context.fillStyle = color;
                context.fillRect(0, 0, canvas.width, canvas.height);
                canvas.touched = false;
            }
            canvas.clearCanvas = function(){
                if (canvas.bgColor) canvas.setBGColor(canvas.bgColor);
                if (canvas.bgImageUrl) {
                    var img = new Image();
                    img.onload=function(){ context.drawImage(img,0,0); }
                    img.src = canvas.bgImageUrl;                    
                }
                canvas.touched = false;
            }
            canvas.bgImageUrl = _this.sfgObject.config.data[jQuery(canvas).attr('name')];
            if (!canvas.bgImageUrl) {
                canvas.bgImageUrl = jQuery(canvas).css('background-image');
                if (canvas.bgImageUrl) canvas.bgImageUrl = canvas.bgImageUrl.toLowerCase().replace('url(', '').replace(')', '').replace(/\"/g, '');
            }
            jQuery(canvas).css('background-image', 'none');
            canvas.bgColor = jQuery(canvas).attr('canvas-background-color');
            
            canvas.setPenSize(jQuery(canvas).attr('canvas-pen-size'));
            canvas.setPenColor(jQuery(canvas).attr('canvas-pen-color'));
            canvas.clearCanvas();
            
            //finger drawing
            var drawer = {
                isDrawing: false,
                touchstart: function (coors) {
                    context.beginPath();
                    context.moveTo(coors.x, coors.y);
                    this.isDrawing = true;
                },
                touchmove: function (coors) {
                    if (this.isDrawing) {
                        context.lineTo(coors.x, coors.y);
                        context.stroke();
                    }
                },
                touchend: function (coors) {
                    if (this.isDrawing) {
                        this.touchmove(coors);
                        this.isDrawing = false;
                        canvas.touched = true;
                    }
                }
            };
            function draw(event) {
                if (!event.targetTouches[0]) return;
                var coors = {
                    x: event.targetTouches[0].pageX,
                    y: event.targetTouches[0].pageY
                };
                var obj = canvas;
                if (obj.offsetParent) {
                    do {
                        coors.x -= obj.offsetLeft;
                        coors.y -= obj.offsetTop;
                    } while ((obj = obj.offsetParent) != null);
                }
                drawer[event.type](coors);
                canvas.touched = true;
            }
            canvas.addEventListener('touchstart', draw, false);
            canvas.addEventListener('touchmove', draw, false);
            canvas.addEventListener('touchend', draw, false);
            canvas.addEventListener('touchmove', function (event) { event.preventDefault(); }, false); 
            
            //mouse drawing
            jQuery(canvas).mousedown(function(mouseEvent) {
                canvas.offset = jQuery(canvas).offset();
                var position = _this.getPosition(mouseEvent, canvas);
                canvas.borderWidth = parseInt(jQuery(canvas).css('border-left-width'));
                canvas.borderHeight = parseInt(jQuery(canvas).css('border-top-width'));
                canvas.kx = (canvas.offsetWidth - canvas.borderWidth * 2) / parseInt(jQuery(canvas).attr('width'));
                context.moveTo(position.X, position.Y);
                context.beginPath();
                jQuery(this).mousemove(function (mouseEvent) {
                    _this.drawLine(mouseEvent, canvas, context);
                }).mouseup(function (mouseEvent) {
                    _this.finishDrawing(mouseEvent, canvas, context);
                }).mouseout(function (mouseEvent) {
                    _this.finishDrawing(mouseEvent, canvas, context);
                });
            });
        },
        getPosition: function(mouseEvent, canvas) {
            var x, y;
            if (mouseEvent.pageX != undefined && mouseEvent.pageY != undefined) {
                x = mouseEvent.pageX;
                y = mouseEvent.pageY;
            } else {
                x = mouseEvent.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
                y = mouseEvent.clientY + document.body.scrollTop + document.documentElement.scrollTop;
            }
            return { X: (x - canvas.offset.left - canvas.borderWidth) / canvas.kx, Y: y - canvas.offset.top - canvas.borderHeight };
        },
        drawLine: function(mouseEvent, canvas, context) {
            var position = this.getPosition(mouseEvent, canvas);
            context.lineTo(position.X, position.Y);
            context.stroke();
        },
        finishDrawing: function(mouseEvent, canvas, context) {
            this.drawLine(mouseEvent, canvas, context);
            context.closePath();
            jQuery(canvas).unbind("mousemove").unbind("mouseup").unbind("mouseout");
            canvas.touched = true;
        }
    }
}

window.getSfgObject = function(el) {
    return jQuery(el).closest('.sfg-form')[0].sfgObject;
}