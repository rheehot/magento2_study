<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\LookbookItem;

use Magento\Backend\App\Action;

class Index extends AbstractLookbookItem
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook');
    }
    
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Lookbook Pro'), __('Lookbook Pro'));
        $resultPage->addBreadcrumb(__('Lookbook Item'), __('Lookbook Item'));
        $resultPage->setActiveMenu('Codazon_Lookbookpro::cdzlookbook_item');
        $resultPage->getConfig()->getTitle()->prepend(__('Lookbook Item'));
        return $resultPage;
    }
}

