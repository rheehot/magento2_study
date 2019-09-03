define([
    'jquery',
    'jquery/ui',
    'owlslider'
], function($){
    var breakPoint = 768;
    $.widget('codazon.featuredLooks', {
        options: {
            productWidth: 200,
            dots: true,
            nav: false,
            top: 40
        },
        _create: function() {
            this._assignVariables();
            this._bindEvents();
        },
        _assignVariables: function() {
            var self = this, conf = this.options;
            this.$points = this.element.find('[data-role="small-item-points"]');
            this.$products = this.element.find('[data-role="product-html-data"]');
            this.$modal = $('<div class="mb-look-container">').appendTo('body');
            this.$lookSlider = this.element.find('[data-role="look-slider"]').owlCarousel({
                items: 1,
                nav: conf.nav,
                dots: conf.dots,
                lazyLoad: true,
                rtl: $('body').hasClass('rtl-layout')
            });
            conf.productWidth = parseFloat(conf.productWidth);
            self.element.find('[data-role="slider-loader"]').remove();
        },
        _bindEvents: function() {
            var self = this, conf = this.options;
            this.$modal.modal({
                innerScroll: false,
                wrapperClass: 'mb-look-modal',
                buttons: [],
                opened: function() {}
            });
            this.$points.find('[data-productid]').each(function() {
                var $point = $(this);
                var productId = $point.data('productid');
                $product = self.$products.find('[data-productid="' + productId + '"]');
                if ($product.length) {
                    var $dropdown = $('<div class="product-container lb-modal-container">').hide().css({position: 'absolute'});
                    $dropdown.appendTo($point);
                    var $cloneProduct = $product.clone();
                    $cloneProduct.appendTo($dropdown);
                    var pw = parseFloat($point.innerWidth());
                    var $parent = $point.parents('[data-role="item"]').first();
                    $point.find('.item-point').click(function() {
                        $point.toggleClass('active');
                        $cloneProduct.find('img.owl-lazy').each(function() {
                            $(this).attr('src', $(this).data('src'));
                            $(this).removeClass('owl-lazy');
                        });
                        if (window.innerWidth >= breakPoint) {
                            var left = $point.data('left'), top = $point.data('top');
                            $dropdown.css({
                                height: 'auto',
                                width: conf.productWidth,
                                left: -(conf.productWidth - pw)/2,
                                top:  conf.top,
                            });
                            $dropdown.fadeToggle(300, 'linear', function() {
                                
                            }).toggleClass('drop-active');
                            var dol = parseFloat($dropdown.offset().left);
                            var eol = parseFloat($parent.offset().left);
                            var ew = parseFloat($parent.innerWidth());
                            var dot = parseFloat($dropdown.offset().top);
                            var dh = parseFloat($dropdown.innerHeight());
                            var eot = parseFloat($parent.offset().top);
                            var eh = parseFloat($parent.innerHeight());
                            
                            if (dol < eol) {
                                var left =  -(conf.productWidth - pw)/2 + (eol - dol) + 10;
                                $dropdown.css({left: left + 'px'});
                            }
                            if ( (dol + conf.productWidth) > (eol + ew)) {
                                var left =  -(conf.productWidth - pw)/2 - ((dol + conf.productWidth) - (eol + ew)) - 10;
                                $dropdown.css({left: left + 'px'});
                            }

                            if (dot + dh > eot + eh) {
                                
                                var top = conf.top - ((dot + dh) - (eot + eh)) - 10;
                                $dropdown.css({top: top + 'px'});
                            }
                        } else {
                            self.$modal.empty();
                            $cloneProduct.clone().appendTo(self.$modal);
                            self.$modal.modal('openModal');
                            $('body').trigger('contentUpdated');
                        }
                    });
                    $('body').on('click', function(e) {
                        if (window.innerWidth >= breakPoint) {
                            if ($point.hasClass('active')) {
                                var $target = $(e.target);
                                if ( !($target.is($point) || $point.has($target).length) ) {
                                    $point.removeClass('active');
                                    $dropdown.hide().removeClass('drop-active');
                                }
                            }
                        }
                    });
                }
            });
            $('body').trigger('contentUpdated');
            var curWidth = window.innerWidth;
            $(window).on('resize', function() {
                if (window.innerWidth != curWidth) {
                    curWidth = window.innerWidth;
                    if (curWidth < breakPoint) {
                        self.element.find('.product-container').hide().parents('.item-hook').first().removeClass('active');
                    } else {
                        self.$modal.modal('closeModal');
                    }
                }
            });
        }
    });
    return $.codazon.featuredLooks; 
});