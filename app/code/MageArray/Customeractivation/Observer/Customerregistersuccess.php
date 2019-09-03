<?php

namespace MageArray\Customeractivation\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;


/**
 * Class Customerregistersuccess
 * @package MageArray\Customeractivation\Observer
 */
class Customerregistersuccess implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var
     */
    protected $_inlineTranslation;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Customerregistersuccess constructor.
     * @param \MageArray\Customeractivation\Helper\Data $dataHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \MageArray\Customeractivation\Helper\Data $dataHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \MageArray\Wholesale\Helper\Data $wdataHelper,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_objectManager = $objectManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_dataHelper = $dataHelper;
        $this->_customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->_wholesaleHelper = $wdataHelper;
        $this->request = $request;
        $this->_escaper = $escaper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_dataHelper->isActive()) {
            return;
        }
        $customer = $observer->getEvent()->getCustomer();
        $data = (array)$this->request->getPost();
        if (isset($data['storeweb'])) {
            $type = $this->_wholesaleHelper->getWholesaleType();
            $website = $this->_wholesaleHelper->getWholesaleWebsites();
            $currentWebsite = $this->_wholesaleHelper->getCurrentWebsiteId();
            $store = $this->_wholesaleHelper->getWholesaleStore();
            $currentStore = $this->_wholesaleHelper->getCurrentStoreId();
            $custGroup = $this->_wholesaleHelper->getWholesaleCustomerGroup();
            $configStoreArray = explode(",", $store);
            $configWebsiteArray = explode(",", $website);
            if ($type == 'w-store') {
                if (in_array($currentStore, $configStoreArray)) {
                    if ($this->_dataHelper->isCustomerActivationByGroup() &&
                        !in_array($custGroup,
                            $this->_dataHelper->getCustomerActivationGroupIds())
                    ) {
                        return;
                    }
                }
            }
            if ($type == 'w-webs') {
                if (in_array($currentWebsite, $configWebsiteArray)) {
                    if ($this->_dataHelper->isCustomerActivationByGroup()
                        && !in_array($custGroup,
                            $this->_dataHelper->getCustomerActivationGroupIds())
                    ) {
                        return;
                    }
                }
            }
        } else {
            if ($this->_dataHelper->isCustomerActivationByGroup()
                && !in_array($customer->getGroupId(),
                    $this->_dataHelper->getCustomerActivationGroupIds())
            ) {
                return;
            }
        }
        $defaultStatus = '';
        if (isset($data['storeweb'])) {
            $type = $this->_wholesaleHelper->getWholesaleType();
            $website = $this->_wholesaleHelper->getWholesaleWebsites();
            $currentWebsite = $this->_wholesaleHelper->getCurrentWebsiteId();
            $store = $this->_wholesaleHelper->getWholesaleStore();
            $currentStore = $this->_wholesaleHelper->getCurrentStoreId();
            $custGroup = $this->_wholesaleHelper->getWholesaleCustomerGroup();
            $configStoreArray = explode(",", $store);
            $configWebsiteArray = explode(",", $website);
            if ($type == 'w-store') {
                if (in_array($currentStore, $configStoreArray)) {
                    $defaultStatus = $this->_dataHelper
                        ->getDefaultActivationStatus($custGroup);
                }
            }
            if ($type == 'w-webs') {
                if (in_array($currentWebsite, $configWebsiteArray)) {
                    $defaultStatus = $this->_dataHelper
                        ->getDefaultActivationStatus($custGroup);
                }
            }
        } else {
            $defaultStatus = $this->_dataHelper
            ->getDefaultActivationStatus($customer->getGroupId());
        }
        

        $custCollection = $this->_objectManager
            ->create('Magento\Customer\Model\Customer')
            ->load($customer->getId());
        $customerData = $custCollection->getDataModel();
        if (!$customerData->getCustomAttribute('is_approved')
            || $customerData
                ->getCustomAttribute('is_approved')
                ->getValue() != $defaultStatus
        ) {
            $customerData->setCustomAttribute('is_approved', $defaultStatus);
            $custCollection->updateData($customerData);
            $custCollection->save();
        }

        if ($defaultStatus == 0) {
            $this->_customerSession->setId(null);
            $registractionSuccessMessage = $this->_dataHelper
                ->getRegistractionSuccessMessageForUser();
            $isSendEmailToAdmin = $this->_dataHelper
                ->isSendEmailToAdmin();
            //echo $isSendEmailToAdmin;echo '<br/>';
            if ($isSendEmailToAdmin == 1) {
                $this->inlineTranslation->suspend();

                try {
                    $adminEmailTemplate = $this->_dataHelper
                        ->getAdminTemplate();
                    $adminEmail = $this->_dataHelper->getAdminEmail();
                    /* echo $adminEmailTemplate;echo '<br/>';
                    echo $adminEmail;echo '<br/>';
                    exit; */
                    $firstName = $customer->getFirstname();
                    $lastName = $customer->getLastname();
                    $customerName = $firstName . " " . $lastName;
                    $data = [
                        'name' => $this->_escaper
                            ->escapeHtml($customerName),
                        'email' => $this->_escaper
                            ->escapeHtml($customer->getEmail()),
                    ];
                    $dataObject = new \Magento\Framework\DataObject();
                    $dataObject->setData($data);

                    $sender = [
                        'name' => $this->_escaper
                            ->escapeHtml($customerName),
                        'email' => $this->_escaper
                            ->escapeHtml($customer->getEmail()),
                    ];

                    $transport = $this->_transportBuilder
                        ->setTemplateIdentifier($adminEmailTemplate)// this code we have mentioned in the email_templates.xml
                        ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ]
                        )
                        ->setTemplateVars(['data' => $dataObject])
                        ->setFrom($sender)
                        ->addTo($adminEmail,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                        ->getTransport();

                    $transport->sendMessage();
                    $this->inlineTranslation->resume();

                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                }
            }

            if (isset($registractionSuccessMessage)
                && !empty($registractionSuccessMessage)
            ) {
                $this->messageManager
                    ->addSuccess(__($registractionSuccessMessage));
            } else {
                $this->messageManager
                    ->addSuccess(__('Thank you for registration. We will review your account and update you once activated.'));
            }
        }

    }
}