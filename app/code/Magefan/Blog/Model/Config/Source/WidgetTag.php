<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Used in recent post widget
 *
 */
class WidgetTag extends Tag
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            parent::toOptionArray();
            array_unshift($this->options, ['label' => __('Please select'), 'value' => 0]);
        }

        return $this->options;
    }
}
