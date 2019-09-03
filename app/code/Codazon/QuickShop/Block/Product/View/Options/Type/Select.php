<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\QuickShop\Block\Product\View\Options\Type;
/**
 * Product options text type block
 *
 * @api
 * @since 100.0.2
 */
class Select extends \Magento\Catalog\Block\Product\View\Options\Type\Select
{
    public function getValuesHtml(): string
    {
        $option = $this->getOption();
        $optionType = $option->getType();
        if ($optionType === \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN ||
            $optionType === \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE
        ) {
            $optionBlock = $this->getLayout()->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Select\Multiple');
        }
        if ($optionType === \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO ||
            $optionType === \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX
        ) {
            $optionBlock = $this->getLayout()->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Select\Checkable');
        }
        return $optionBlock
            ->setOption($option)
            ->setProduct($this->getProduct())
            ->setSkipJsReloadPrice(1)
            ->_toHtml();
    }
}
