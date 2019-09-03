<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteAutocomplete\Model\Search;

use \MageWorx\SearchSuiteAutocomplete\Helper\Data as HelperData;
use \Magento\Search\Helper\Data as SearchHelper;
use \Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use \Magento\Framework\ObjectManagerInterface as ObjectManager;
use \Magento\Search\Model\QueryFactory;
use \MageWorx\SearchSuiteAutocomplete\Model\Source\AutocompleteFields;
use \MageWorx\SearchSuiteAutocomplete\Model\Source\ProductFields;

/**
 * Product model. Return product data used in search autocomplete
 */
class Product implements \MageWorx\SearchSuiteAutocomplete\Model\SearchInterface
{
    /**
     * @var \MageWorx\SearchSuiteAutocomplete\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Search\Helper\Data
     */
    protected $searchHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    private $queryFactory;

    /**
     * Product constructor.
     *
     * @param HelperData $helperData
     * @param SearchHelper $searchHelper
     * @param LayerResolver $layerResolver
     * @param ObjectManager $objectManager
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        HelperData $helperData,
        SearchHelper $searchHelper,
        LayerResolver $layerResolver,
        ObjectManager $objectManager,
        QueryFactory $queryFactory
    ) {
    
        $this->helperData = $helperData;
        $this->searchHelper = $searchHelper;
        $this->layerResolver = $layerResolver;
        $this->objectManager = $objectManager;
        $this->queryFactory = $queryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        $responseData['code'] = AutocompleteFields::PRODUCT;
        $responseData['data'] = [];

        if (!$this->canAddToResult()) {
            return $responseData;
        }

        $queryText = $this->queryFactory->get()->getQueryText();
        $productResultFields = $this->helperData->getProductResultFieldsAsArray();
        $productResultFields[] = ProductFields::URL;

        $productCollection = $this->getProductCollection($queryText);

        foreach ($productCollection as $product) {
            $responseData['data'][] = array_intersect_key($this->getProductData($product), array_flip($productResultFields));
        }

        $responseData['size'] = $productCollection->getSize();
        $responseData['url'] = ($productCollection->getSize() > 0) ? $this->searchHelper->getResultUrl($queryText) : '';

        return $responseData;
    }

    /**
     * Retrive product collection by query text
     *
     * @param  string $queryText
     * @return mixed
     */
    protected function getProductCollection($queryText)
    {
        $productResultNumber = $this->helperData->getProductResultNumber();

        $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);

        $productCollection = $this->layerResolver->get()
            ->getProductCollection()
            ->addAttributeToSelect([ProductFields::DESCRIPTION, ProductFields::SHORT_DESCRIPTION])
            ->addSearchFilter($queryText);
        
        /* custom */
        $request =  $this->objectManager->get('\Magento\Framework\App\Request\Http');
        if ($catId = $request->getParam('cat')) {
            //$category = $this->objectManager->create('Magento\Catalog\Model\Category')->load($catId);
            $category = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection')
                ->addFieldToFilter('entity_id', $catId)
                ->addAttributeToSelect(['is_anchor'])
                ->getFirstItem();
            if ($category) {
                $productCollection->addCategoryFilter($category);
            }
        }
        /* custom */

        $productCollection->getSelect()->limit($productResultNumber);

        return $productCollection;
    }

    /**
     * Retrieve all product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProductData($product)
    {
        /** @var \MageWorx\SearchSuiteAutocomplete\Block\Autocomplete\Product $product */
        $product = $this->objectManager->create('MageWorx\SearchSuiteAutocomplete\Block\Autocomplete\ProductAgregator')
            ->setProduct($product);

        $data = [
            ProductFields::NAME => $product->getName(),
            ProductFields::SKU => $product->getSku(),
            ProductFields::IMAGE => $product->getSmallImage(),
            ProductFields::REVIEWS_RATING => $product->getReviewsRating(),
            ProductFields::SHORT_DESCRIPTION => $product->getShortDescription(),
            ProductFields::DESCRIPTION => $product->getDescription(),
            ProductFields::PRICE => $product->getPrice(),
            ProductFields::ADD_TO_CART => $product->getAddToCartData(),
            ProductFields::URL => $product->getUrl()
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function canAddToResult()
    {
        return in_array(AutocompleteFields::PRODUCT, $this->helperData->getAutocompleteFieldsAsArray());
    }
}
