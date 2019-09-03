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
class OptionsBoxState implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray($addEmpty = true)
    {
        return [
            ['value' => '',     'label' => __('Close all boxes')],
            ['value' => '1',    'label' => __('Open first box')],
            ['value' => '2',    'label' => __('Open all boxes')],
        ];
    }
}
