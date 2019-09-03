/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
			quickShop: 'Codazon_QuickShop/js/quickshop'
        }
    },
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'Codazon_QuickShop/js/configurable-mixin': true
            }
        }
    }
};
