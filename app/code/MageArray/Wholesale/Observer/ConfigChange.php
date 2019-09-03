<?php
namespace MageArray\Wholesale\Observer;

use Magento\Framework\Event\ObserverInterface;

class ConfigChange implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \MageArray\Wholesale\Helper\Data $dataHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->redirect = $redirect;
        $this->_wholesaleHelper = $dataHelper;
        $this->_resourceConfig = $resourceConfig;
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $type = $this->_wholesaleHelper->getWholesaleType();
        $website = $this->_wholesaleHelper->getWholesaleWebsites();
        $stores = $this->_wholesaleHelper->getWholesaleStore();
        $hidePriceCatalog = $this->_wholesaleHelper->getHidePriceCatalog();
        $custActivation = $this->_wholesaleHelper->getCustomerActivation();
        $custGroup = $this->_wholesaleHelper->getWholesaleCustomerGroup();
        $configWebsiteArray = explode(",", $website);
        $configStoreArray = explode(",", $stores);
        if ($type == 'w-store' && $stores) {
            if (count($configStoreArray)) {
                foreach ($configStoreArray as $configStore) {
                    if ($hidePriceCatalog == 1) {
                        $this->_resourceConfig->saveConfig(
                            'hideprice/general/active',
                            1,
                            'stores',
                            $configStore
                        );

                        $this->_resourceConfig->saveConfig(
                            'hideprice/general/display_text',
                            'Login To View Price',
                            'stores',
                            $configStore
                        );

                        $this->_resourceConfig->saveConfig(
                            'hideprice/general/url',
                            'customer/account/login',
                            'stores',
                            $configStore
                        );

                        $this->_resourceConfig->saveConfig(
                            'hideprice/general/enable_customer_group',
                            0,
                            'stores',
                            $configStore
                        );
                    }
                    
                    if ($hidePriceCatalog == 2) {
                        $this->_resourceConfig->saveConfig(
                            'logincatalog/general/enable',
                            1,
                            'stores',
                            $configStore
                        );

                        $this->_resourceConfig->saveConfig(
                            'logincatalog/general/redirect_to_catalog',
                            1,
                            'stores',
                            $configStore
                        );

                        $this->_resourceConfig->saveConfig(
                            'logincatalog/general/redirect_to_product',
                            1,
                            'stores',
                            $configStore
                        );

                        $this->_resourceConfig->saveConfig(
                            'logincatalog/general/redirect_to_catalogsearch',
                            1,
                            'stores',
                            $configStore
                        );
                    }

                    if ($custActivation == 1) {
                        $this->_resourceConfig->saveConfig(
                            'customeractivation/general/active',
                            1,
                            'stores',
                            $configStore
                        );

                        $this->_resourceConfig->saveConfig(
                            'customeractivation/customers/activationforgroup',
                            1,
                            'stores',
                            $configStore
                        );

                        $this->_resourceConfig->saveConfig(
                            'customeractivation/customers/requireactivationgroup',
                            $custGroup,
                            'stores',
                            $configStore
                        );
                    }
                }
            }
        }

        if ($type == 'w-webs' && $website) {
            if (count($configWebsiteArray)) {
                foreach ($configWebsiteArray as $configWebsite) {
                    if ($hidePriceCatalog == 1) {
                        $this->_resourceConfig->saveConfig(
                            'hideprice/general/active',
                            1,
                            'websites',
                            $configWebsite
                        );

                        $this->_resourceConfig->saveConfig(
                            'hideprice/general/display_text',
                            'Login To View Price',
                            'websites',
                            $configWebsite
                        );

                        $this->_resourceConfig->saveConfig(
                            'hideprice/general/url',
                            'customer/account/login',
                            'websites',
                            $configWebsite
                        );

                        $this->_resourceConfig->saveConfig(
                            'hideprice/general/enable_customer_group',
                            0,
                            'websites',
                            $configWebsite
                        );
                    }

                    if ($hidePriceCatalog == 2) {
                        $this->_resourceConfig->saveConfig(
                            'logincatalog/general/enable',
                            1,
                            'websites',
                            $configWebsite
                        );

                        $this->_resourceConfig->saveConfig(
                            'logincatalog/general/redirect_to_catalog',
                            1,
                            'websites',
                            $configWebsite
                        );

                        $this->_resourceConfig->saveConfig(
                            'logincatalog/general/redirect_to_product',
                            1,
                            'websites',
                            $configWebsite
                        );

                        $this->_resourceConfig->saveConfig(
                            'logincatalog/general/redirect_to_catalogsearch',
                            1,
                            'websites',
                            $configWebsite
                        );
                    }

                    if ($custActivation == 1) {
                        $this->_resourceConfig->saveConfig(
                            'customeractivation/general/active',
                            1,
                            'websites',
                            $configWebsite
                        );
                        $this->_resourceConfig->saveConfig(
                            'customeractivation/customers/activationforgroup',
                            1,
                            'websites',
                            $configWebsite
                        );
                        $this->_resourceConfig->saveConfig(
                            'customeractivation/customers/requireactivationgroup',
                            $custGroup,
                            'websites',
                            $configWebsite
                        );
                    }
                }
            }
        }

        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->clean();
        }

    }
}