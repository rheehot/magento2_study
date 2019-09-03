define([
    "jquery", "jquery/ui", "cdz_slider", 'domReady!', "toggleAdvanced", "matchMedia", 'mage/tabs'
], function($) {
    $.fn._buildToggle = function() {
        $("[data-cdz-toggle]").each(function() {
            $(this).toggleAdvanced({
                selectorsToggleClass: "active",
                baseToggleClass: "expanded",
                toggleContainers: $(this).data('cdz-toggle'),
            });
        });

    };
    $.fn._buildTabs = function() {
        if ($('.cdz-tabs').length > 0) {
            $('.cdz-tabs').each(function() {
                var $tab = $(this);
                mediaCheck({
                    media: '(min-width: 768px)',
                    // Switch to Desktop Version
                    entry: function() {
                        $tab.tabs({
                            openedState: "active",
                            openOnFocus: true,
                            collapsible: false,
                        });
                    },
                    // Switch to Mobile Version
                    exit: function() {
                        $tab.tabs({
                            openedState: "active",
                            openOnFocus: false,
                            collapsible: true
                        });
                    }
                });
            });
        }
    };

    $.fn._buildSlider = function() {
        if ($('.cdz-slider').length > 0) {
            $('.cdz-slider').each(function() {
                var $owl = $(this);
                if ((typeof $owl.data('no_slider') == 'undefined') || (!$owl.data('noslider'))) {
                    $owl.addClass('owl-carousel');
                    var sliderItem = typeof($owl.data('items')) !== 'undefined' ? $owl.data('items') : 5;
                    $owl.owlCarousel({
                        loop: typeof($owl.data('loop')) !== 'undefined' ? $owl.data('loop') : true,
                        margin: typeof($owl.data('margin')) !== 'undefined' ? $owl.data('margin') : 0,
                        responsiveClass: true,
                        nav: typeof($owl.data('nav')) !== 'undefined' ? $owl.data('nav') : true,
                        dots: typeof($owl.data('dots')) !== 'undefined' ? $owl.data('dots') : false,
                        items: sliderItem,
                        autoWidth: typeof($owl.data('autoWidth')) !== 'undefined' ? $owl.data('autoWidth') : false,
                        rtl: ThemeOptions.rtl_layout == 1 ? true : false,
                        responsive: {
                            0: {
                                items: typeof($owl.data('items-0')) !== 'undefined' ? $owl.data('items-0') : sliderItem
                            },
                            480: {
                                items: typeof($owl.data('items-480')) !== 'undefined' ? $owl.data('items-480') : sliderItem
                            },
                            768: {
                                items: typeof($owl.data('items-768')) !== 'undefined' ? $owl.data('items-768') : sliderItem
                            },
                            1024: {
                                items: typeof($owl.data('items-1024')) !== 'undefined' ? $owl.data('items-1024') : sliderItem
                            },
                            1280: {
                                items: typeof($owl.data('items-1280')) !== 'undefined' ? $owl.data('items-1280') : sliderItem
                            },
                            1440: {
                                items: typeof($owl.data('items-1440')) !== 'undefined' ? $owl.data('items-1440') : sliderItem
                            }
                        }
                    });
                    var center = typeof($owl.data('center')) !== 'undefined' ? $owl.data('center') : null;
                    if(center != null){
                        $owl.on('changed.owl.carousel', function(e) {
                            $owl.find(".owl-item").removeClass('center');
                            var item = $owl.find(".owl-item").get(e.item.index+1);
                            $(item).addClass('center');
                        });
                        var item = $owl.find(".owl-item").get(center);
                        $(item).addClass('center');   
                    }
                }
            });
        }
    };

    $.fn._tooltip = function() {
        var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if(iOS == false){
            $('.show-tooltip').each(function() {
                $(this).tooltip({
                    position: {
                        my: "center top-80%",
                        at: "center top",
                        using: function(position, feedback) {
                            $(this).css(position);
                            $(this).addClass("cdz-tooltip");
                        }
                    }
                });
            })
        }
    };

    $.fn._fixBlogSearch = function(){
        $("#blog_search_mini_form button.search").prop("disabled", false);
    }

    $.fn._fixHoverIos = function(){
        if ('createTouch' in document) {
            try {
              var ignore = RegExp(':hover');
              for (var i = 0; i < document.styleSheets.length; i++) {
                var sheet = document.styleSheets[i];
                if (!sheet.cssRules) {
                  continue;
                }
                for (var j = sheet.cssRules.length - 1; j >= 0; j--) {
                  var rule = sheet.cssRules[j];
                  if (rule.type === CSSRule.STYLE_RULE && ignore.test(rule.selectorText)) {
                    sheet.deleteRule(j);
                  }
                }
              }
            }
            catch(e) {
            }
        }
    }

    $.fn._buildSlider();
    $.fn._buildTabs();
    $.fn._tooltip();
    $.fn._buildToggle();
    $.fn._fixBlogSearch();
    setTimeout($.fn._fixBlogSearch,500);


});