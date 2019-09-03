<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxLayeredNavPro\Model\Config\Source;

/**
 * Config category source
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class AttributeExtraStyles implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray($addEmpty = true)
    {
        return [
            ['value' => '',         'label' => __('Default Style')],
            ['value' => 'slider',   'label' => __('Slider')],
            ['value' => 'dropdown', 'label' => __('Dropdown')],
            ['value' => 'checkbox',  'label' => __('Check Box')],
        ];
    }
}
