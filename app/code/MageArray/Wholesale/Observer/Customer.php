<?php
namespace MageArray\Wholesale\Observer;

use Magento\Framework\Event\ObserverInterface;

class Customer implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \MageArray\Wholesale\Helper\Data $dataHelper
    ) {
        $this->redirect = $redirect;
        $this->_wholesaleHelper = $dataHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager
            ->create('Magento\Customer\Model\Session');

        $type = $this->_wholesaleHelper->getWholesaleType();
        $website = $this->_wholesaleHelper->getWholesaleWebsites();
        $currentWebsite = $this->_wholesaleHelper->getCurrentWebsiteId();
        $configWebsiteArray = explode(",", $website);

        if (!$customerSession->isLoggedIn() && $type != 'none') {
            if ($type == 'w-webs') {
                if (in_array($currentWebsite, $configWebsiteArray)) {
                    $controller = $observer->getControllerAction();
                    $this->redirect->redirect(
                        $controller->getResponse(),
                        'wholesale/account/create'
                    );
                }
            }
        }

    }
}