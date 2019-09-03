<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxLayeredNavPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use \Magento\Framework\App\ObjectManager;
/**
 * Layer attribute filter
 */
class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    private $tagFilter;
    
    protected $objectManager;
    
    protected $helper;
    
    protected $enableMultiSelect;
    
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );
        $this->tagFilter = $tagFilter;
        $this->objectManager = ObjectManager::getInstance();
        $this->helper = $this->objectManager->get('Codazon\AjaxLayeredNavPro\Helper\Data');
        $this->enableMultiSelect = $this->helper->enableMultiSelect();
    }   
    
    public function applyToCollection($productCollection, $request, $requestVar)
    {
        $attributeValue = $request->getParam($requestVar);
        if ($attributeValue) {
            $attributeValuesArray = explode(',', (string)$attributeValue);
            $productCollection->addFieldToFilter($requestVar, ['in' => $attributeValuesArray]);
        }
        return $productCollection;
    }
    
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        
        if ($this->enableMultiSelect) {
            //$this->setBeforeApplyFacetedData($this->helper->getBeforeApplyFacetedData($productCollection, $attribute));
        }

        $attributeValuesArray = explode(',', (string)$attributeValue);
        $or = [];
        foreach($attributeValuesArray as $val){
            $or[] = ['eq' => $val];
        }
        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $or);
        $label = $this->getOptionText($attributeValue);
        $this->getLayer()
            ->getState()
            ->addFilter($this->_createItem($label, $attributeValue));

        if (!count($this->getAttributeModel()->getFrontend()->getSelectOptions())) {
            $this->setItems([]);
        }
        
        if ((!$this->enableMultiSelect) || $request->getParam('cdz_disable_'.$attribute->getAttributeCode())) {
            $this->setItems([]); // set items to disable show filtering
        }
        return $this;
    }
    
    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
     protected function _getItemsData()
     {
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        
        $productCollection = $this->getLayer()
            ->getProductCollection();
        
        if ($this->getBeforeApplyFacetedData()) {
            $optionsFacetedData = $this->getBeforeApplyFacetedData();
        } else {
            $optionsFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());
        }

        $isAttributeFilterable =
            $this->getAttributeIsFilterable($attribute) === static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS;

        //if (!$this->enableMultiSelect) {
            if (count($optionsFacetedData) === 0 && !$isAttributeFilterable) {
                return $this->itemDataBuilder->build();
            }
        //}

        $productSize = $productCollection->getSize();

        $options = $attribute->getFrontend()
            ->getSelectOptions();
        foreach ($options as $option) {
            $this->buildOptionData($option, $isAttributeFilterable, $optionsFacetedData, $productSize);
        }

        return $this->itemDataBuilder->build();
    }
    
    /**
     * Build option data
     *
     * @param array $option
     * @param boolean $isAttributeFilterable
     * @param array $optionsFacetedData
     * @param int $productSize
     * @return void
     */
    private function buildOptionData($option, $isAttributeFilterable, $optionsFacetedData, $productSize)
    {
        
        $value = $this->getOptionValue($option);
        if ($value === false) {
            return;
        }
        $count = $this->getOptionCount($value, $optionsFacetedData);
        
        if (!$this->enableMultiSelect) {
            if ($isAttributeFilterable && (!$this->isOptionReducesResults($count, $productSize) || $count === 0)) {
                return;
            }
        } else {
            if ($isAttributeFilterable && ($count === 0)) {
                return;
            }
        }

        $this->itemDataBuilder->addItemData(
            $this->tagFilter->filter($option['label']),
            $value,
            $count
        );
    }
    
    /**
     * Retrieve option value if it exists
     *
     * @param array $option
     * @return bool|string
     */
    private function getOptionValue($option)
    {
        if (empty($option['value']) && !is_numeric($option['value'])) {
            return false;
        }
        return $option['value'];
    }
    
    /**
     * Retrieve count of the options
     *
     * @param int|string $value
     * @param array $optionsFacetedData
     * @return int
     */
    private function getOptionCount($value, $optionsFacetedData)
    {
        return isset($optionsFacetedData[$value]['count'])
            ? (int)$optionsFacetedData[$value]['count']
            : 0;
    }
}
