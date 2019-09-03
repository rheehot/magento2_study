<?php
namespace MageArray\Wholesale\Block\Links;

class RegisterWholesale extends \Magento\Framework\View\Element\Html\Link
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \MageArray\Wholesale\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_wholesaleHelper = $dataHelper;
    }

    public function isCustomerLogin()
    {
        $custSession = $this->_objectManager
            ->create('\Magento\Customer\Model\Session');
        $data = 0;
        if ($custSession->isLoggedIn()) {
            $data = 1;
        }
        
        return $data;
    }

    public function isShow()
    {
        $type = $this->_wholesaleHelper->getWholesaleType();
        $store = $this->_wholesaleHelper->getWholesaleStore();
        $configStoreArray = explode(",", $store);
        $currentStore = $this->_wholesaleHelper->getCurrentStoreId();
        $show = 0;
        if ($type != 'none') {
            if ($type == 'w-store') {
                if (in_array($currentStore, $configStoreArray)) {
                    $show = 1;
                }
            }
        }
        
        return $show;
    }
}
