<?php
/**
 *
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageArray\Customeractivation\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class ApproveStatus
 * @package MageArray\Customeractivation\Controller\Adminhtml\Customer
 */
class ApproveStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectmanager;
    /**
     * @var \MageArray\Customeractivation\Helper\Data
     */
    protected $_datahelper;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlinetranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportbuilder;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \MageArray\Customeractivation\Helper\Data $dataHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_objectmanager = $context->getObjectManager();
        $this->_datahelper = $dataHelper;
        $this->_inlinetranslation = $inlineTranslation;
        $this->_escaper = $escaper;
        $this->_transportbuilder = $transportBuilder;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */

    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $custCollection = $this->_objectmanager
            ->create('Magento\Customer\Model\Customer')
            ->load($customerId);
        $customerData = $custCollection
            ->getDataModel();

        $currentStatus = $customerData
            ->getCustomAttribute('is_approved');

        $statusValue = ($currentStatus && $currentStatus->getValue() == 1) ? 0 : 1;
        $customerData->setCustomAttribute('is_approved',
            $statusValue);
        $custCollection->updateData($customerData);
        $custCollection->save();

        $isSendApprovalEmailToCustomer = $this->_datahelper
            ->isSendApprovalEmailToCustomer();

        if ($statusValue
            && $isSendApprovalEmailToCustomer
        ) {

            $senderDetails = $this->_datahelper
                ->getSenderdetail();

            $this->_inlinetranslation
                ->suspend();
            try {
                $custAprvlEmailTemplate = $this->_datahelper
                    ->getcustAprvlEmailTemplate();
                $fname = $customerData->getFirstname();
                $lname = $customerData->getLastname();
                $customerName = $fname . " " . $lname;
                $data = [
                    'name' => $this->_escaper
                        ->escapeHtml($customerName),
                    'email' => $this->_escaper
                        ->escapeHtml($customerData->getEmail()),
                ];
                $dataObject = new \Magento\Framework\DataObject();
                $dataObject->setData($data);

                $sender = $senderDetails;

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $transport = $this->_transportbuilder
                    ->setTemplateIdentifier($custAprvlEmailTemplate)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars(['data' => $dataObject])
                    ->setFrom($sender)
                    ->addTo($customerData->getEmail(), $storeScope)
                    ->getTransport();
                $transport->sendMessage();;
                $this->_inlinetranslation->resume();

            } catch (\Exception $e) {
                $this->messageManager
                    ->addSuccess(__($e->getMessage()));
            }
        }

        $this->messageManager->addSuccess(__($statusValue ? 'Customer account approved successfully' : 'Customer account disapproved successfully'));
        $resultRedirect = $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect
            ->setPath('customer/index/edit', ['id' => $customerId]);
    }
}
