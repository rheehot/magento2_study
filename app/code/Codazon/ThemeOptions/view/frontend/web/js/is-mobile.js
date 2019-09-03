define(['jquery', 'domReady!'], function($, doc) {
    'use strict';

    var isMobile = {
        Android: function() {
	        return navigator.userAgent.match(/Android/i);
	    },
	    BlackBerry: function() {
	        return navigator.userAgent.match(/BlackBerry/i);
	    },
	    iPhone: function() {
	        return navigator.userAgent.match(/iPhone|iPod/i);
	    },
	    iPad: function() {
	        return navigator.userAgent.match(/iPad/i);
	    },
	    Opera: function() {
	        return navigator.userAgent.match(/Opera Mini/i);
	    },
	    Windows: function() {
	        return navigator.userAgent.match(/IEMobile/i);
	    },
	    any: function() {
	        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iPhone() || isMobile.Opera() || isMobile.Windows());
	    }
    };

    return isMobile;
});