<?php
namespace MageArray\LoginCatalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class ControllerPredispatch implements ObserverInterface
{

    const ROUTE_PART_MODULE = 0;
    const ROUTE_PART_CONTROLLER = 1;
    const ROUTE_PART_ACTION = 2;
    private $_redirectSetFlag = false;

    private $_disabledRoutes = null;

    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \MageArray\LoginCatalog\Helper\Data $dataHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlinterface,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->redirect = $redirect;
        $this->_logincatalogHelper = $dataHelper;
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->_urlinterface = $urlinterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_logincatalogHelper->getRedirectFromAllPage()) {

            $this->_handlePossibleRedirect($observer);
        }

        if ($this->_logincatalogHelper->getRedirectCatalog()) {
            if ($this->_requestedRouteMatches(['catalog', 'category', 'view'])) {
                $this->_handlePossibleRedirect($observer);
            }
        }

        if ($this->_logincatalogHelper->getRedirectProduct()) {
            if ($this->_requestedRouteMatches(['catalog', 'product', 'view'])) {
                $this->_handlePossibleRedirect($observer);
            }
        }

        if ($this->_logincatalogHelper->getRedirectCatalogSearch()) {
            if ($this->_requestedRouteMatches(
                ['catalogsearch', 'result', 'index']
            )) {
                $this->_handlePossibleRedirect($observer);
            }
        }
    }

    private function _handlePossibleRedirect($observer)
    {
        if (!$this->_logincatalogHelper->getModuleEnable()) {
            return;
        }

        $customerSession = $this->_objectManager
            ->create('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            return;
        }

        if ($this->_isNotApplicableForRequest()) {
            return;
        }

        if ($this->_logincatalogHelper->getRedirectToPage() == 2) {
            $identifier = $this->_logincatalogHelper->getRedirectToCms();
            $currUrl = $this->_urlinterface->getCurrentUrl();
            if (strpos($currUrl, $identifier) !== false) {
                return;
            }
        }

        $this->_setAfterAuthUrl();

        $url = $this->_getredirecttargeturl();
        $message = $this->_logincatalogHelper->getMessage();
        $this->messageManager->addError($message);
        $observer->getControllerAction()
            ->getResponse()
            ->setRedirect($url);
        $this->_redirectSetFlag = true;
    }

    private function _getRedirectTargetUrl()
    {
        if ($this->_logincatalogHelper->getRedirectToPage() == 2) {
            return $this->_getCmsPageRedirectTargetUrl();
        } else {
            return $this->_getLoginPageRedirectTargetUrl();
        }
    }

    private function _getLoginPageRedirectTargetUrl()
    {
        $customRedirectionUrl = $this->_urlinterface
            ->getUrl('customer/account/login');
        return $customRedirectionUrl;
    }

    private function _getCmsPageRedirectTargetUrl()
    {
        $cmsId = $this->_logincatalogHelper->getRedirectToCms();
        $storeId = $this->_logincatalogHelper->getCurrentStoreId();
        $page = $this->_objectManager->create('Magento\Cms\Model\Page');
        $page->setStoreId($storeId)
            ->load($cmsId, 'identifier');
        if (!$page->getId()) {
            $message = __('Invalid CMS page configured as a redirect landing page.');
            throw new \Magento\Framework\Validator\Exception($message);
        }

        $url = $this->_urlinterface->getUrl($page->getIdentifier());
        return $url;
    }

    private function _setAfterAuthUrl()
    {
        $currUrl = $this->_urlinterface->getCurrentUrl();
        $customerSession = $this->_objectManager
            ->create('Magento\Customer\Model\Session');
        $customerSession->setAfterAuthUrl($currUrl);
        $customerSession->authenticate();
    }

    private function _isNotApplicableForRequest()
    {
        return
            $this->_redirectSetFlag ||
            $this->_isLoginPageRequest() ||
            $this->_isApiRequest() ||
            $this->_isRedirectDisabledForRoute();
    }

    private function _isLoginPageRequest()
    {
        return
            $this->_requestedRouteMatches(['wholesale', 'account', 'create']) ||
            $this->_requestedRouteMatches(['customer', 'account', 'login']) ||
            $this->_requestedRouteMatches(['customer', 'account', 'loginPost']) ||
            $this->_requestedRouteMatches(['customer', 'account', 'create']) ||
            $this->_requestedRouteMatches(['customer', 'account', 'createPost']) ||
            $this->_requestedRouteMatches(['customer', 'account', 'index']);
    }

    private function _isApiRequest()
    {
        return $this->_requestedRouteMatches(['api']);
    }

    private function _requestedRouteMatches(array $route)
    {
        switch (count($route)) {
            case 1:
                return $this->_moduleMatches($route);
            case 2:
                return $this->_moduleAndControllerMatches($route);
            case 3:
                return $this->_moduleAndControllerAndActionMatches($route);
            default:
                return false;
        }
    }

    private function _moduleMatches(array $route)
    {
        $moduleName = $this->request->getModuleName();
        return $moduleName === $route[self::ROUTE_PART_MODULE];
    }

    private function _moduleAndControllerMatches(array $route)
    {
        $controllerName = $this->request->getControllerName();
        return $this->_moduleMatches($route) 
        && $controllerName === $route[self::ROUTE_PART_CONTROLLER];
    }

    private function _moduleAndControllerAndActionMatches(array $route)
    {
        $actionName = $this->request->getActionName();
        return $this->_moduleAndControllerMatches($route) && 
        $actionName === $route[self::ROUTE_PART_ACTION];
    }

    private function _isRedirectDisabledForRoute()
    {
        if (!isset($this->_disabledRoutes)) {
            $this->_initializeListOfDisabledRoutes();
        }

        foreach ($this->_disabledRoutes as $route) {
            if ($this->_requestedRouteMatches($route)) {
                return true;
            }
        }

        return false;
    }

    private function _initializeListOfDisabledRoutes()
    {
        $this->_disabledRoutes = [];
        if ($routes = $this->_logincatalogHelper->getDisableRoute()) {
            foreach (explode("\n", $routes) as $route) {
                $this->_disabledRoutes[] = explode('/', trim($route));
            }
        }
    }
}