<?php
namespace MageArray\Wholesale\Observer;

use Magento\Framework\Event\ObserverInterface;

class RegisterSuccess implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \MageArray\Wholesale\Helper\Data $dataHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->redirect = $redirect;
        $this->_wholesaleHelper = $dataHelper;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $data = (array)$this->request->getPost();
        $custId = $customer->getId();
        if (isset($data['storeweb'])) {
            $type = $this->_wholesaleHelper->getWholesaleType();
            $website = $this->_wholesaleHelper->getWholesaleWebsites();
            $currentWebsite = $this->_wholesaleHelper->getCurrentWebsiteId();
            $store = $this->_wholesaleHelper->getWholesaleStore();
            $currentStore = $this->_wholesaleHelper->getCurrentStoreId();
            $custGroup = $this->_wholesaleHelper->getWholesaleCustomerGroup();
            $configStoreArray = explode(",", $store);
            $configWebsiteArray = explode(",", $website);
            $groupActivation = $this->_scopeConfig->getValue(
                'customeractivation/customers/requireactivationgroup',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $custCollection = $this->_objectManager
                ->create('Magento\Customer\Model\Customer')->load($custId);
            if ($type == 'w-store') {
                if (in_array($currentStore, $configStoreArray)) {
                    try {
                        if ($custGroup) {
                            $customer->setGroupId($custGroup);
                            $custCollection->setData('group_id', $custGroup);
                            $custCollection->setData('taxvat', $data['taxvat']);
                            if ($groupActivation) {
                                $custCollection->setIsApproved(0);
                            }

                            $custCollection->save();
                        }

                    } catch (Exception $e) {
                        $this->logger
                            ->info("observer error: " . $e->getMessage());
                    }
                }
            }
            
            if ($type == 'w-webs') {
                if (in_array($currentWebsite, $configWebsiteArray)) {
                    try {
                        if ($custGroup) {
                            $customer->setGroupId($custGroup);
                            $custCollection->setData('group_id', $custGroup);
                            $custCollection->setData('taxvat', $data['taxvat']);
                            if ($groupActivation) {
                                $custCollection->setIsApproved(0);
                            }

                            $custCollection->save();
                        }

                    } catch (Exception $e) {
                        $this->logger
                            ->info("observer error: " . $e->getMessage());
                    }
                }
            }

        }
    }
}