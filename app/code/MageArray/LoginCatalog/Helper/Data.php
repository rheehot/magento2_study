<?php
namespace MageArray\LoginCatalog\Helper;

use Magento\Framework\Module\ModuleListInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLE = 'logincatalog/general/enable';
    const XML_PATH_HIDE_NAVIGATION = 'logincatalog/general/hide_navigation';
    const XML_PATH_REDIRECT_FROM_ALL_PAGE = 'logincatalog/general/redirect_from_all_page';
    const XML_PATH_REDIRECT_TO_CATALOG = 'logincatalog/general/redirect_to_catalog';
    const XML_PATH_REDIRECT_TO_PRODUCT = 'logincatalog/general/redirect_to_product';
    const XML_PATH_REDIRECT_TO_CATALOGSEARCH = 'logincatalog/general/redirect_to_catalogsearch';
    const XML_PATH_MESSAGE = 'logincatalog/general/message';
    const XML_PATH_REDIRECT_TO_PAGE = 'logincatalog/general/redirect_to_page';
    const XML_PATH_REDIRECT_TO_CMS = 'logincatalog/general/redirect_to_cms';
    const XML_PATH_DISABLE_ROUTE = 'logincatalog/general/disable_route';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ModuleListInterface $moduleList
    ) {
        $this->_moduleList = $moduleList;
        $this->_productAttributeRepository = $productAttributeRepository;
        $this->_date = $date;
        $this->_localeDate = $localeDate;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getModuleEnable($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getHideNavigation($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HIDE_NAVIGATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getRedirectFromAllPage($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECT_FROM_ALL_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getMessage($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getRedirectCatalog($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECT_TO_CATALOG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getRedirectProduct($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECT_TO_CATALOG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getRedirectCatalogSearch($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECT_TO_CATALOG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getRedirectToPage($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECT_TO_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getRedirectToCms($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECT_TO_CMS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getDisableRoute($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DISABLE_ROUTE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }
}
