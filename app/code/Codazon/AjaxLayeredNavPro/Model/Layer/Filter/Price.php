<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxLayeredNavPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use \Magento\Framework\App\ObjectManager;
/**
 * Layer price filter based on Search API
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\CatalogSearch\Model\Layer\Filter\Price
{
    /** Price delta for filter  */
    const PRICE_DELTA = 0.001;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Search\Dynamic\Algorithm
     */
    private $priceAlgorithm;
    
    protected $objectManager;
    
    protected $helper;
    
    protected $enableMultiSelect;
    
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        array $data = []
    ) {

        $this->priceCurrency = $priceCurrency;
        $this->resource = $resource;
        $this->customerSession = $customerSession;
        $this->priceAlgorithm = $priceAlgorithm;
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->objectManager = ObjectManager::getInstance();
        $this->helper = $this->objectManager->get('Codazon\AjaxLayeredNavPro\Helper\Data');
        $this->enableMultiSelect = $this->helper->enableMultiSelect();
    }
    
    public function applyToCollection($productCollection, $request, $requestVar)
    {
        $filter = $request->getParam($requestVar);
        if ($filter) {
            if (!$filter || is_array($filter)) {
                return $this;
            }
            $filterParams = explode(',', $filter);
            $filter = $this->dataProvider->validateFilter($filterParams[0]);
            if (!$filter) {
                return $this;
            }
            $this->dataProvider->setInterval($filter);
            $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
            if ($priorFilters) {
                $this->dataProvider->setPriorIntervals($priorFilters);
            }
            list($from, $to) = $filter;
            $productCollection->addFieldToFilter(
                'price',
                ['from' => $from, 'to' =>  empty($to) || $from == $to ? $to : $to - self::PRICE_DELTA]
            );
        }
        return $productCollection;
    }

    private function prepareData($key, $count)
    {
        list($from, $to) = explode('_', $key);
        if ($from == '*') {
            $from = $this->getFrom($to);
        }
        if ($to == '*') {
            $to = $this->getTo($to);
        }
        $label = $this->_renderRangeLabel($from, $to);
        $value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();

        $data = [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];

        return $data;
    }
    
    
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        $filterParams = explode(',', $filter);
        $filter = $this->dataProvider->validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        $this->dataProvider->setInterval($filter);
        $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
        if ($priorFilters) {
            $this->dataProvider->setPriorIntervals($priorFilters);
        }

        list($from, $to) = $filter;
        $to += 0.01;

        $attribute = $this->getAttributeModel();
        $productCollection = $this->getLayer()->getProductCollection();
        
        if ($this->enableMultiSelect) {
            $this->setBeforeApplyFacetedData($this->helper->getBeforeApplyFacetedData($productCollection, $attribute, $this));
        }
        
        $productCollection->addFieldToFilter(
            'price',
            ['from' => $from, 'to' =>  empty($to) || $from == $to ? $to : $to - self::PRICE_DELTA]
        );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
        );

        return $this;
    }
    
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        //$facets = $productCollection->getFacetedData($attribute->getAttributeCode());
        
        if ($this->getBeforeApplyFacetedData()) {
            $facets = $this->getBeforeApplyFacetedData();
        } else {
            $facets = $productCollection->getFacetedData($attribute->getAttributeCode());
        }
        
        $data = [];
        if (count($facets) > 1) { // two range minimum
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation['count'];
                if (strpos($key, '_') === false) {
                    continue;
                }
                $data[] = $this->prepareData($key, $count, $data);
            }
        }

        return $data;
    }
}
