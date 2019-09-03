/**
 * Copyright Â© 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery', 'jquery/ui', 'Codazon_AjaxLayeredNavPro/js/layout'], function($) {
    
   !function(o){if(o.support.touch="ontouchend"in document,o.support.touch){var t,e=o.ui.mouse.prototype,u=e._mouseInit,n=e._mouseDestroy;function c(o,t){if(!(o.originalEvent.touches.length>1)){o.preventDefault();var e=o.originalEvent.changedTouches[0],u=document.createEvent("MouseEvents");u.initMouseEvent(t,!0,!0,window,1,e.screenX,e.screenY,e.clientX,e.clientY,!1,!1,!1,!1,0,null),o.target.dispatchEvent(u)}}e._touchStart=function(o){!t&&this._mouseCapture(o.originalEvent.changedTouches[0])&&(t=!0,this._touchMoved=!1,c(o,"mouseover"),c(o,"mousemove"),c(o,"mousedown"))},e._touchMove=function(o){t&&(this._touchMoved=!0,c(o,"mousemove"))},e._touchEnd=function(o){t&&(c(o,"mouseup"),c(o,"mouseout"),this._touchMoved||c(o,"click"),t=!1)},e._mouseInit=function(){var t=this;t.element.bind({touchstart:o.proxy(t,"_touchStart"),touchmove:o.proxy(t,"_touchMove"),touchend:o.proxy(t,"_touchEnd")}),u.call(t)},e._mouseDestroy=function(){var t=this;t.element.unbind({touchstart:o.proxy(t,"_touchStart"),touchmove:o.proxy(t,"_touchMove"),touchend:o.proxy(t,"_touchEnd")}),n.call(t)}}}($);
    
    $.widget('codazon.ajaxlayerednavpro', {
        options: {
            ajaxSelector: '.swatch-option-link-layered, .block-content.filter-content a.action.remove, .filter-options-content a, a.action.clear.filter-clear, .toolbar-products .pages-items a, .sidebar .options .items .item a',
            modeControl: '[data-role="mode-switcher"]',
            directionControl: '[data-role="direction-switcher"]',
            orderControl: '[data-role="sorter"]',
            limitControl: '[data-role="limiter"]',
            mode: 'product_list_mode',
            direction: 'product_list_dir',
            order: 'product_list_order',
            limit: 'product_list_limit',
            modeDefault: 'grid',
            directionDefault: 'asc',
            orderDefault: 'position',
            limitDefault: '9',
            url: ''
        },
        _create: function() {
            this.options.url = window.location.href;
            var self = this, conf = this.options;
            this.initRange = {};
            this._prepareHtml();
            this._attacheEvents();
            this._history();
            setTimeout(function() {
                self._modifyFunction();
            }, 500);
        },

        _history: function(){
            var self = this;
            $(document).ready(function() {
                if (window.history && window.history.pushState) {
                    $(window).on('popstate', function() {
                        var vars = self._getUrlVars();
                        var catId = vars['cat'];
                        var ajaxUrl = window.location.href;
                        if(catId){
                            var tmps = catId.split('_');
                            var id = tmps[0];
                            self.options.url = window.location.href;
                            ajaxUrl = self.changeUrl('cat', id, '');
                            console.log(ajaxUrl);
                        }
                        self._ajaxLoad(ajaxUrl, true, true);
                    });
                }
            });
        },

        // Read a page's GET URL variables and return them as an associative array.
        _getUrlVars: function ()
        {
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for(var i = 0; i < hashes.length; i++)
            {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        },

        _prepareHtml: function() {
            var self = this, conf = this.options;
            $('[data-role=filter-slider-container]').each(function() {
                var $container = $(this), $slider = $container.find('[data-role=filter-slider]');
                var data = $container.data('filter');
                var $min = $container.find('[data-role=min-value]'), $max = $container.find('[data-role=max-value]');
                var min = 0, max = (data.valuesRange.length - 1), curMin, curMax;
                var step = 1;
                var sliderOptions = {
                    range: data.range,  //true
                    min: min*step,
                    max: max*step,
                    //step: step,
                    values: [data.min*step, data.max*step],
                    slide: function(event, ui) {
                        curMin = Math.round(ui.values[0]/step);
                        curMax = Math.round(ui.values[1]/step);
                        $min.text(data.valuesRange[curMin].label);
                        $max.text(data.valuesRange[curMax].label);
                    },
                    stop: function(event, ui) {
                        curMin = Math.round(ui.values[0]/step);
                        curMax = Math.round(ui.values[1]/step);
                        var ajaxUrl = data.action;
                        var value = [];
                        for (var i=curMin; i <= curMax; i++) {
                            value.push(data.valuesRange[i].value);
                        }
                        if (value.length) {
                            value = value.join(',');
                            ajaxUrl += (ajaxUrl.search(/\?/) != -1) ? '&' : '?';
                            ajaxUrl += data.code + '=' + value;
                        }
                        self.activeCode = data.code;
                        self._ajaxLoad(ajaxUrl);
                    }
                }
                $.ui.slider(sliderOptions, $slider);
            });
            $('[data-role=filter-dropdown]').on('change', function() {
                var $select = $(this);
                var ajaxUrl = $select.val();
                self.activeCode = $select.data('code');
                self._ajaxLoad(ajaxUrl);
            });
            $('[data-role=filter-checkbox-container] [type=checkbox]').on('change', function() {
                var $checkbox = $(this), $container = $checkbox.parents('[data-role=filter-checkbox-container]').first();
                var data = $container.data('filter');
                var value = [];
                var ajaxUrl = data.action;
                $container.find('[type=checkbox]:checked').each(function() {
                    value.push($(this).val());
                });
                if (value.length) {
                    value = value.join(',');
                    ajaxUrl += (ajaxUrl.search(/\?/) != -1) ? '&' : '?';
                    ajaxUrl += data.code + '=' + value;
                }
                self.activeCode = data.code;
                self._ajaxLoad(ajaxUrl);
            });
            $('[data-role=price-slider-container]').each(function(){
                var $container = $(this), $slider = $container.find('[data-role=price-slider]'),
                $rate = $container.find('[data-role=rate]'),
                $min = $container.find('[data-role=min_price]'), $max = $container.find('[data-role=max_price]'),
                $form = $container.find('[data-role=price-form]').first(), code = $form.data('code'), $priceInput = $form.find('[name=' + code + ']'),
                rate = parseFloat($rate.val()),
                min = parseFloat($min.val()), max = parseFloat($max.val()), curMin, curMax;
                var data = $container.data('filter');
                if (!self.initRange[code]) {
                    self.initRange[code] = {min: data.minValue, max: data.maxValue};
                }                
                if (self.initRange[code].max < max) {
                    self.initRange[code].max = max;
                }
                //var step = 1;
                var step = max;
                var sliderOptions = {
                    //range: true,
                    min: self.initRange[code].min * step,
                    max: self.initRange[code].max * step,
                    values: [min * step, max * step],
                    // step: step,
                    slide: function(event, ui) {
                        curMin = (ui.values[0] / step);
                        curMax = (ui.values[1] / step);
                        $min.val(curMin);
                        $max.val(curMax);
                        $priceInput.val(curMin + '-' + curMax);
                    },
                    stop: function(event, ui) {
                        curMin = (ui.values[0] / step);
                        curMax = (ui.values[1] / step);
                        $min.val(curMin);
                        $max.val(curMax);
                        $priceInput.val(curMin + '-' + curMax);
                        $form.submit();
                    }
                };
                $form.on('submit', function(e) {
                    e.preventDefault();
                    if ($form.valid()) {
                        curMin = $min.val()/rate;
                        curMax = $max.val()/rate;
                        $priceInput.val(curMin + '-' + curMax);
                        var ajaxUrl = $form.attr('action');
                        ajaxUrl += (ajaxUrl.search(/\?/) != -1) ? '&' : '?';
                        ajaxUrl += code + '=' + $priceInput.val();
                        $form.validation();
                        self.activeCode = code;
                        self._ajaxLoad(ajaxUrl);
                    }
                });
                $.ui.slider(sliderOptions, $slider);
            });
        },
        _moveToolbar: function(selector){
            $(selector + ":first").replaceWith($(selector + ":last"));
        },
        _modifyFunction: function() {
            var self = this, conf = this.options;
            $.codazon.categoryLayout({},$('body'));
            $('.toolbar.toolbar-products').each(function() {
                var $toolbar = $(this);
                var toolbarForm = $toolbar.data('mageProductListToolbarForm');

                if (toolbarForm) {
                    /*toolbarForm.changeUrl = function (paramName, paramValue, defaultValue) {
                        var decode = window.decodeURIComponent,
                        urlPaths = this.options.url.split('?'),
                        baseUrl = urlPaths[0],
                        urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                        paramData = {},
                        parameters, i;

                        for (i = 0; i < urlParams.length; i++) {
                            parameters = urlParams[i].split('=');
                            paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                            decode(parameters[1].replace(/\+/g, '%20')) :
                            '';
                        }
                        paramData[paramName] = paramValue;

                        if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
                            delete paramData[paramName];
                        }
                        paramData = $.param(paramData);
                        
                        var ajaxUrl = baseUrl + (paramData.length ? '?' + paramData : '');
                        self._ajaxLoad(ajaxUrl, true);
                    }*/
                    self._bind($(toolbarForm.options.modeControl), toolbarForm.options.mode, toolbarForm.options.modeDefault);
                    self._bind($(toolbarForm.options.directionControl), toolbarForm.options.direction, toolbarForm.options.directionDefault);
                    self._bind($(toolbarForm.options.orderControl), toolbarForm.options.order, toolbarForm.options.orderDefault);
                    self._bind($(toolbarForm.options.limitControl), toolbarForm.options.limit, toolbarForm.options.limitDefault);
                }
            });
            $(window).trigger('resize'); 
            $.fn._tooltip();
            setTimeout(function() {
                $('body').trigger('layeredNavLoaded');
            }, 500);
        },
        _bind: function (element, paramName, defaultValue) {
            element.unbind();
            if (element.is('select')) {
                element.on('change', {
                    paramName: paramName,
                    'default': defaultValue
                }, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {
                    paramName: paramName,
                    'default': defaultValue
                }, $.proxy(this._processLink, this));
            }
        },
        _processLink: function (event) {
            var self = this;
            event.preventDefault();
            var url = this.changeUrl(
                event.data.paramName,
                $(event.currentTarget).data('value'),
                event.data.default
            );
            self._ajaxLoad(url, true);
        },
        _processSelect: function (event) {
            var self = this;
            var url = this.changeUrl(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default
            );
            self._ajaxLoad(url, true);
        },
        changeUrl: function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent,
                urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters, i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                    decode(parameters[1].replace(/\+/g, '%20')) :
                    '';
            }
            paramData[paramName] = paramValue;

            if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
                //delete paramData[paramName];
            }
            paramData = $.param(paramData);

            return baseUrl + (paramData.length ? '?' + paramData : '');
        },
        _attacheEvents: function() {
            var self = this, conf = this.options;
            $(conf.ajaxSelector).on('click', function(e) {
                e.preventDefault();
                var $a = $(this);
                var ajaxUrl = $a.attr('href');
                if ($a.parents('.toolbar-products').length) {
                    self._ajaxLoad(ajaxUrl, true);
                } else {
                    self._ajaxLoad(ajaxUrl, false);
                }
                
            });
        },
        _ajaxLoad: function(ajaxUrl, needSrollTop, backButton) {
            this.options.url = ajaxUrl;
            var self = this, conf = this.options;
            if ((!ajaxUrl) || (ajaxUrl.search('javascript:') == 0) || (ajaxUrl.search('#') == 0)) {
                return;
            }
            if (!needSrollTop) {
                needSrollTop = false;
            }
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {ajax_nav: 1},
                showLoader: true,
                success: function(res) {
                    if (res.catalog_leftnav) {
                        $('.block.filter').first().replaceWith(res.catalog_leftnav);
                    }
                    if (res.category_products) {
                        var $listContainer = $('#product-list-container');
                        $listContainer.html(decodeURIComponent(res.category_products));
                        if (needSrollTop) {
                            $(window).scrollTop($listContainer.offset().top - 60);
                        }
                    }
                    if (res.page_main_title) {
                        $('.page-title-wrapper').first().replaceWith(res.page_main_title);
                    }
                    if(!backButton){
                        if (res.updated_url) {
                            window.history.pushState(res.updated_url, document.title, res.updated_url);
                        } else {
                            window.history.pushState(ajaxUrl, document.title, ajaxUrl);
                        }
                    }

                    $('body').trigger('contentUpdated');

                    if(res.toolbar){
                        $('.toolbar.toolbar-products:first').replaceWith(res.toolbar);
                    }

                    self._prepareHtml();
                    self._attacheEvents();
                    setTimeout(function() {
                        self._modifyFunction();
                    }, 100);
                    if (window.innerWidth >= 768) {
                        setTimeout(function() {
                            if (self.activeCode) {
                                $('.filter-options-item').each(function(i, el) {
                                    var $collapsible = $(this);
                                    if ($collapsible.hasClass(self.activeCode)) {
                                        $('#narrow-by-list').data('mageAccordion').activate(i);
                                        return false;
                                    }
                                });
                            }
                        }, 100);
                    }
                }
            });
        }
    });
    return $.codazon.ajaxlayerednavpro;
});