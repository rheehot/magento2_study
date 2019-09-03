<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageArray\Customeractivation\Controller\Adminhtml\Customeractivation;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

/**
 * Class MassDelete
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectmanager;
    /**
     * @var \MageArray\Customeractivation\Helper\Data
     */
    protected $_datahelper;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlinetranslation;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportbuilder;


    /**
     * MassStatus constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \MageArray\Customeractivation\Helper\Data $dataHelper
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \MageArray\Customeractivation\Helper\Data $dataHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_objectmanager = $context->getObjectManager();
        $this->_datahelper = $dataHelper;
        $this->_inlinetranslation = $inlineTranslation;
        $this->_escaper = $escaper;
        $this->_transportbuilder = $transportBuilder;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->_filter
            ->getCollection($this->_collectionFactory->create());
        $collectionSize = $collection->getSize();
        $statusValue = $this->getRequest()
            ->getParam('status', 0);

        $totalCustomer = 0;
        if ($collectionSize) {
            $isSendApprovalEmailToCustomer = $this->_datahelper
                ->isSendApprovalEmailToCustomer();
            $sender = $this->_datahelper
                ->getSenderdetail();

            foreach ($collection as $customer) {
                $custCollection = $this
                    ->_objectmanager
                    ->create('Magento\Customer\Model\Customer')
                    ->load($customer->getId());
                $customerData = $custCollection->getDataModel();
                $totalCustomer++;

                if ($customerData->getCustomAttribute('is_approved')
                    && $customerData->getCustomAttribute('is_approved')
                        ->getValue() == $statusValue
                ) {
                    continue;
                }
                $customerData->setCustomAttribute('is_approved',
                    $statusValue);
                $custCollection->updateData($customerData);
                $custCollection->save();

                if ($isSendApprovalEmailToCustomer == 1
                    && $statusValue == 1
                ) {
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

                        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                        $transport = $this->_transportbuilder
                            ->setTemplateIdentifier($custAprvlEmailTemplate)
                            ->setTemplateOptions(
                                [
                                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                ])
                            ->setTemplateVars(['data' => $dataObject])
                            ->setFrom($sender)
                            ->addTo($customerData->getEmail(),
                                $storeScope)
                            ->getTransport();
                        $transport->sendMessage();;
                        $this->_inlinetranslation->resume();

                    } catch (\Exception $e) {
                        $this->messageManager
                            ->addSuccess(__($e->getMessage()));
                    }
                }
            }
        }

        if ($totalCustomer) {
            $this->messageManager
                ->addSuccess(__('A total of %1 record(s) were updated.',
                    $totalCustomer));
        } else {
            $this->messageManager->addSuccess(__('Please selecte customer.'));
        }
        $resultRedirect = $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect
            ->setPath('customer/index');
    }
}
