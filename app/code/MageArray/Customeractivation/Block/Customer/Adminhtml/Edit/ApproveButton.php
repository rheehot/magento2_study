<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageArray\Customeractivation\Block\Customer\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ResetButton
 */
class ApproveButton extends \Magento\Customer\Block\Adminhtml\Edit\GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $_customerRegistry;
    /**
     * @var \MageArray\Customeractivation\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \MageArray\Customeractivation\Helper\Data $dataHelper
    ) {
        parent::__construct($context, $registry);
        $this->_dataHelper = $dataHelper;
        $this->_customerRegistry = $customerRegistry;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
       
        $customerId = $this->getCustomerId();

        if ($customerId) {
            $customer = $this->_customerRegistry->retrieve($customerId);
            $data = [
                'label' => __(($customer->getIsApproved()) ? 'Disapprove' : 'Approve'),
                'class' => 'approve-customer',
                'on_click' => sprintf("location.href = '%s';",
                            $this->getUnlockUrl()),
                'sort_order' => 50,
            ];
        }
        return $data;
    }

    /**
     * Returns customer unlock action URL
     *
     * @return string
     */
    protected function getUnlockUrl()
    {
        return $this->getUrl('customeractivation/customer/approveStatus',
            ['customer_id' => $this->getCustomerId()]);
    }
}
