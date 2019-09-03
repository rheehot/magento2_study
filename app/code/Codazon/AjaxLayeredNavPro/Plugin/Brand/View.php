<?php
/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Codazon\AjaxLayeredNavPro\Plugin\Brand;

class View
{
    protected $helper;
    
    public function __construct(
        \Codazon\AjaxLayeredNavPro\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    public function afterExecute($controller, $page)
    {   
        if ($controller->getRequest()->getParam('ajax_nav')) {
            $request = $controller->getRequest();
            $layout = $page->getLayout();
            $result = [];
            if ($block = $layout->getBlock('category.products')) {
                $result['category_products'] = rawurlencode($block->toHtml());
            }
            if ($block = $layout->getBlock('catalog.leftnav')) {
                $result['catalog_leftnav'] = $block->toHtml();
            }
            if ($block = $layout->getBlock('page.main.title')) {
                $result['page_main_title'] = $block->toHtml();
            }
            $filterManager = $this->helper->getFilterManager();
            $queryValue = $request->getQueryValue();
            $newQueryValue = $queryValue;
            if ($block = $layout->getBlock('catalog.navigation.state')) {
                $filters = $block->getActiveFilters();
                $urlParams = [];
                foreach($filters as $filter) {
                    $filterModel = $filter->getFilter();
                    $code = $filterModel->getRequestVar();
                    if (isset($newQueryValue[$code])) {
                        $class = get_class($filterModel);
                        if ($class == 'Magento\CatalogSearch\Model\Layer\Filter\Attribute' || $class == 'Codazon\AjaxLayeredNavPro\Model\Layer\Filter\Attribute') {
                            $label = $filter->getLabel();
                            if (is_array($label)) {
                                $newQueryValue[$code] = [];
                                foreach ($label as $lb) {
                                    $newQueryValue[$code][] = $filterManager->translitUrl(htmlspecialchars_decode($lb));
                                }
                                $newQueryValue[$code] = trim(implode(',', $newQueryValue[$code]));
                            } else {
                                $newQueryValue[$code] = $filterManager->translitUrl(htmlspecialchars_decode($label));
                            }
                        } elseif ($class == 'Magento\CatalogSearch\Model\Layer\Filter\Category') {
                            $newQueryValue[$code] = $newQueryValue[$code].'_'.$filterManager->translitUrl(htmlspecialchars_decode($filter->getLabel()));
                        }
                    }
                }
                
                if (isset($newQueryValue['cat'])) {
                    if ($request->getParam('id') == $newQueryValue['cat']) {
                        $newQueryValue['cat'] = null;
                    }
                }
                $result['updated_url'] = $block->getUrl('*/*/*', [
                    '_current'      => true,
                    '_query'        => $newQueryValue,
                    '_use_rewrite'  => true,
                ]);
                $result['updated_url'] = str_replace('%2C', ',', $result['updated_url']);
            }
            $json = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\Controller\Result\JsonFactory')->create();
            $json->setData($result);
            return $json;
        } else {
            return $page;
        }
        return $page;
    }
}
