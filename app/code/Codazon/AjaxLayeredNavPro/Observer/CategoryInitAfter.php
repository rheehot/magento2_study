<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\AjaxLayeredNavPro\Observer;


use Magento\Framework\Event\ObserverInterface;


class CategoryInitAfter implements ObserverInterface
{
    protected $helper;
    
    protected $filterAbleAttributeList;

    public function __construct(
        \Codazon\AjaxLayeredNavPro\Helper\Data $helper,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterAbleAttributeList
    ) {
        $this->helper = $helper;
        $this->filterAbleAttributeList = $filterAbleAttributeList;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getData('controller_action');
        $request = $controller->getRequest();
        $queryValue = $request->getQueryValue();
        if (count($queryValue)) {
            $filterList = $this->filterAbleAttributeList->getList();
            $filterManager = $this->helper->getFilterManager();
            foreach ($queryValue as $code => $labels) {
                $labels = explode(',', $labels);
                if (count($labels) > 1) {
                    $optionValue = [];
                    foreach ($labels as $label) {
                        if ($item = $filterList->getItemByColumnValue('attribute_code', $code)) {
                            foreach ($item->getSource()->getAllOptions() as $key => $option) {
                                if ($filterManager->translitUrl(htmlspecialchars_decode($option['label'])) === $label) {
                                    $optionValue[] = $option['value'];
                                }
                            }
                            
                        }
                    }
                    if (count($optionValue)) {
                        $optionValue = implode(',', $optionValue);
                        $request->setParam($code, $optionValue);
                    }
                } else {
                    $label = $labels[0];
                    if ($item = $filterList->getItemByColumnValue('attribute_code', $code)) {
                        foreach ($item->getSource()->getAllOptions() as $key => $option) {
                            if ($filterManager->translitUrl(htmlspecialchars_decode($option['label'])) === $label) {
                                $request->setParam($code, $option['value']);
                                break;
                            }
                        }
                    }
                }
            }
            
        }
    }
}
