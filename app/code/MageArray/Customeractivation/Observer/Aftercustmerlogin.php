<?php

namespace MageArray\Customeractivation\Observer;

use Magento\Customer\Model\Customer;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

/**
 * Class Aftercustmerlogin
 * @package MageArray\Customeractivation\Observer
 */
class Aftercustmerlogin implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \MageArray\Customeractivation\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Aftercustmerlogin constructor.
     * @param \MageArray\Customeractivation\Helper\Data $dataHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \MageArray\Customeractivation\Helper\Data $dataHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_dataHelper->isActive()) {
            return;
        }
        /**
         * @var Customer
         */
        $customer = $observer->getEvent()->getCustomer();

        if ($this->_dataHelper->isCustomerActivationByGroup()
            && !in_array($customer->getGroupId(),
                $this->_dataHelper
                    ->getCustomerActivationGroupIds())
        ) {
            return;
        }

        $customerData = $customer->getDataModel();
        $status = $customerData->getCustomAttribute('is_approved');

        if (!$status || $status->getValue() == 0) {
            $this->_customerSession->setId(null);
            $errorMessage = $this->_dataHelper
                ->getErrorMessageForUser();
            if (!$errorMessage) {
                $errorMessage = 'Your account need approval.';
            }
            $this->_messageManager->addError(__($errorMessage));
        }
        return;
    }
}