<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\AjaxLayeredNavPro\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $filterManager; 
    
    const ENABLE = 'codazon_ajaxlayerednavpro/general/enable';
    const ENABLE_PRICE_SLIDER = 'codazon_ajaxlayerednavpro/general/enable_price_slider';
    
    protected $enable;
    
    protected $layout;
    
    protected $block = \Magento\LayeredNavigation\Block\Navigation\FilterRenderer::class;
    
    protected $swatchBlock = \Magento\Swatches\Block\LayeredNavigation\RenderLayered::class;
    
    protected $swatchHelper;
    
    protected $objectManager;
    
    protected $_filters;
    
    protected $enableMultiSelect;
    
    protected function getSwatchHelper() {
        if (null === $this->swatchHelper) {
            $this->swatchHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Swatches\Helper\Data'
            );
        }
        return $this->swatchHelper;
    }
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        parent::__construct($context);
        $this->swatchHelper = $swatchHelper;
        $this->layout = $layout;
        $this->filterManager = $filterManager;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }
    
    public function getLayout()
    {
        if (null === $this->layout) {
            $this->layout = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\View\LayoutInterface');
        }
        return $this->layout;
    }
    
    public function getRate()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        
        //you can also pass INR code here insted of below current store currency
        $currentCurrency = $storeManager->getStore()->getCurrentCurrency()->getCode();

        $rate = $storeManager->getStore()->getBaseCurrency()->getRate($currentCurrency);
        return $rate;
    }
    
    public function getFilterManager()
    {
        return $this->filterManager;
    }

    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, 'store');
    }
    
    public function enableAjaxLayeredNavigation()
    {
        if ($this->enable === null) {
            $this->enable = (bool)$this->scopeConfig->getValue(self::ENABLE, 'store');
        }
        return $this->enable;
    }
    
    public function enablePriceSlider()
    {
        return $this->scopeConfig->getValue(self::ENABLE_PRICE_SLIDER, 'store');
    }
    
    public function extractExtraOptions($attributeObject)
    {
        if ($extraOptions = $attributeObject->getData('extra_options')) {
            $extraOptions = json_decode($extraOptions, true);
            if (!empty($extraOptions['custom_style'])) {
                $attributeObject->addData([
                    'custom_style' => $extraOptions['custom_style']
                ]);
            }
        }
    }
    
    public function getFilterHtml($filter, $customStyle)
    {
        $block = $this->block;
        $isSwatchAttribute = $this->swatchHelper->isSwatchAttribute($filter->getAttributeModel());
        if ($isSwatchAttribute && ($customStyle == 'checkbox')) {
            $block = $this->swatchBlock;
        }
        
        $attributeModel = $filter->getAttributeModel();
        if (($customStyle == 'slider') && ($attributeModel->getFrontendInput() == 'price')) {
            $customStyle = 'price-slider';
        }
        return $this->getLayout()->createBlock($block)
            ->setTemplate('Codazon_AjaxLayeredNavPro::layer/custom-style/'.$customStyle.'.phtml')
            ->setOptionsFilter($filter)
            ->setSwatchFilter($filter)
            ->setIsSwatchAttribute($isSwatchAttribute)
            ->setData('custom_style', $customStyle)
            ->toHtml();
    }
    
    public function getItemsValuesRange($filter)
    {
        $filterItems = $filter->getItems();
        $items = [];
        if (count($filterItems)) {
            $i = 0;
            foreach ($filterItems as $filterItem) {
                $items[$i] = [
                    'value'     => $filterItem->getValue(),
                    'label'     => $filterItem->getLabel(),
                ];
                $i++;
            }
        }
        return $items;
    }
    
    public function getFilterAction($filter)
    {
        $query = $this->_request->getQueryValue();
        $code = $filter->getRequestVar();
        $query[$code] = null;
        $action = $this->_urlBuilder->getUrl('*/*/*', [
            '_current'      => true,
            '_use_rewrite'  => true,
            '_query'        => $query
        ]);
        return $action;
    }
    
    public function getMinMaxOfRange($filter)
    {
        $filterItems = $filter->getItems();
        $code = $filter->getRequestVar();
        $values = $this->_request->getParam($code);
        $items = [];
        $count = count($filterItems);
        foreach ($filterItems as $filterItem) {
            $items[] = $filterItem->getValue();
        }
        $min = 0;
        $max = 0;
        if ($values) {
            $values = explode(',', $values);
            for ($i = 0; $i < $count; $i++) {
                if (in_array($items[$i], $values)) {
                    $min = $i; break;
                }
            }
            for ($i = ($count - 1); $i >= 0; $i--) {
                if (in_array($items[$i], $values)) {
                    $max = $i; break;
                }
            }
        } else {
            if ($count) {
                return [0, $count - 1];
            }
        }
        return [$min, $max];
    }
    
    public function getFilters()
    {
        if (null === $this->_filters) {
            if ($this->_request->getFullActionName() === 'catalogsearch_result_index') {
                $this->_filters = $this->objectManager->get('Magento\LayeredNavigation\Block\Navigation\Search')->getFilters();
            } else {
                $this->_filters = $this->objectManager->get('Magento\LayeredNavigation\Block\Navigation\Category')->getFilters();
            }
        }
        return $this->_filters;
    }
    
    public function getBeforeApplyFacetedData($collection, $attribute, $currentFilter = null)
    {
        $cloneCollection = clone $collection;
        $cloneFilterBuilder = clone $this->objectManager->get(\Magento\Framework\Api\FilterBuilder::class);
        $cloneCollection->setFilterBuilder($cloneFilterBuilder);
        
        $cloneSearchCriteriaBuilder = clone $this->objectManager->get(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        $cloneCollection->setSearchCriteriaBuilder($cloneSearchCriteriaBuilder);
        
        foreach ($this->getFilters() as $filter) {
            if ($filter->getRequestVar() != $attribute->getAttributeCode()) {
                if (method_exists($filter, 'applyToCollection')) {
                    $filter->applyToCollection($cloneCollection, $this->_request, $filter->getRequestVar());
                }
            }
        }
        if ($currentFilter) {
            $currentFilter->setBeforeApplyCollection($cloneCollection);
        }
        return $cloneCollection->getFacetedData($attribute->getAttributeCode());
    }
    
    public function enableMultiSelect()
    {
        if (null === $this->enableMultiSelect) {
            $this->enableMultiSelect = ((bool)$this->getConfig('codazon_ajaxlayerednavpro/general/enable_multiselect')) && ((bool)$this->enableAjaxLayeredNavigation());
        }
        return $this->enableMultiSelect;
    }
}
