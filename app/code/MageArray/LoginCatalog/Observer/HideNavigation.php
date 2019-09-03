<?php
namespace MageArray\LoginCatalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class HideNavigation implements ObserverInterface
{

    const ROUTE_PART_MODULE = 0;
    const ROUTE_PART_CONTROLLER = 1;
    const ROUTE_PART_ACTION = 2;
    
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \MageArray\LoginCatalog\Helper\Data $dataHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlinterface,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->redirect = $redirect;
        $this->_logincatalogHelper = $dataHelper;
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->_urlinterface = $urlinterface;
        $this->_responseFactory = $responseFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerSession = $this->_objectManager
            ->create('Magento\Customer\Model\Session');
        if ($this->_logincatalogHelper->getHideNavigation() &&
            $this->_logincatalogHelper->getModuleEnable() &&
            !$customerSession->isLoggedIn()
        ) {
            $menu = $observer->getData('menu');
            foreach ($menu->getChildren() as $key => $node) {
                if (strpos($key, 'category-') === 0) {
                    $menu->removeChild($node);
                }
            }
        }
    }
}