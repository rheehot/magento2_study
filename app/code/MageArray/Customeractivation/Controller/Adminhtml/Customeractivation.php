<?php
namespace MageArray\Customeractivation\Controller\Adminhtml;

/**
 * Class Customeractivation
 * @package MageArray\Customeractivation\Controller\Adminhtml
 */
/**
 * Class Customeractivation
 * @package MageArray\Customeractivation\Controller\Adminhtml
 */
abstract class Customeractivation extends \Magento\Backend\App\Action
{

    /**
     * Customeractivation constructor.
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('MageArray_Customeractivation::customeractivation');
    }
}
