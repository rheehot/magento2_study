<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Model\Config\Reader;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Store implements \Magento\Framework\App\Config\Scope\ReaderInterface
{
    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    protected $_scopePool;

    /**
     * @var \Magento\Store\Model\Config\Converter
     */
    protected $_converter;

    /**
     * @var \Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     * @param \Magento\Framework\App\Config\ScopePool $scopePool
     * @param \Magento\Store\Model\Config\Converter $converter
     * @param \Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Codazon\ThemeOptions\Framework\App\Config\Initial $initialConfig,
        \Codazon\ThemeOptions\Framework\App\Config\ScopePool $scopePool,
        \Magento\Store\Model\Config\Converter $converter,
        \Codazon\ThemeOptions\Model\ResourceModel\Config\Collection\ScopedFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_scopePool = $scopePool;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Read configuration by code
     *
     * @param null|string $code
     * @return array
     * @throws NoSuchEntityException
     */
    public function read($code = null)
    {
        $config = $this->_initialConfig->getData("stores|{$code}");

        //print_r("stores|{$code}");die;
        $collection = $this->_collectionFactory->create(
            ['scope' => \Magento\Store\Model\ScopeInterface::SCOPE_STORES, 'scopeId' => $code]
        );
        $dbStoreConfig = [];
        foreach ($collection as $item) {
        	//if($item->getValue()){
            	$dbStoreConfig[$item->getPath()] = $item->getValue();
            //}
        }
        return $this->_converter->convert($dbStoreConfig, $config);
    }
}
