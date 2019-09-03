<?php
namespace MageArray\Wholesale\Helper;

use Magento\Framework\Module\ModuleListInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_WHOLESALE_TYPE = 'wholesale/general/wholesale_type';
    const XML_PATH_WHOLESALE_STORE = 'wholesale/general/wholesale_store';
    const XML_PATH_WHOLESALE_SELECT_TYPE = 'wholesale/general/wholesale_select_type';
    const XML_PATH_WHOLESALE_WEBSITES = 'wholesale/general/wholesale_websites';
    const XML_PATH_SHOW_ADDRESS = 'wholesale/general/show_address';
    const XML_PATH_TAXVAT_SHOW = 'wholesale/general/taxvat_show';
    const XML_PATH_WHOLESALE_CUSTOMER = 'wholesale/general/wholesale_customer';
    const XML_PATH_WHOLESALE_HIDE_PRICECATALOG = 'wholesale/general/wholesale_hide_pricecatalog';
    const XML_PATH_WHOLESALE_CUSTOMER_ACTIVATION = 'wholesale/general/wholesale_customer_activation';

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
    
    public function getWholesaleType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WHOLESALE_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getWholesaleStore($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WHOLESALE_STORE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getWholesaleSelectType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WHOLESALE_SELECT_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getWholesaleWebsites($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WHOLESALE_WEBSITES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getShowAddress($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_ADDRESS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getShowTaxvat($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TAXVAT_SHOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getWholesaleCustomerGroup($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WHOLESALE_CUSTOMER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getHidePriceCatalog($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WHOLESALE_HIDE_PRICECATALOG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getCustomerActivation($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WHOLESALE_CUSTOMER_ACTIVATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }

    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }
}
