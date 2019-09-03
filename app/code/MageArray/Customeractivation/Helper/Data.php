<?php
namespace MageArray\Customeractivation\Helper;

/**
 * Class Data
 * @package MageArray\Customeractivation\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     */
    const XML_PATH_ACTIVE =
        'customeractivation/general/active';
    /**
     *
     */
    const XML_PATH_DEFAULTACTIVATION =
        'customeractivation/customers/defaultactivation';
    /**
     *
     */
    const XML_PATH_ACTIVATIONFORGROUP =
        'customeractivation/customers/activationforgroup';
    /**
     *
     */
    const XML_PATH_REQUIREACTIVATIONGROUP =
        'customeractivation/customers/requireactivationgroup';
    /**
     *
     */
    const XML_PATH_ALERTADMIN =
        'customeractivation/admincon/alertadmin';
    /**
     *
     */
    const XML_PATH_ADMINEMAILTEMPLATE =
        'customeractivation/admincon/adminemailtemplate';
    /**
     *
     */
    const XML_PATH_ADMINEMAIL = 'customeractivation/admincon/adminemail';
    /**
     *
     */
    const XML_PATH_ALERTCUSTOMER =
        'customeractivation/customersemail/alertcustomer';
    /**
     *
     */
    const XML_PATH_WELCOMEEMAILTEMPLATE =
        'customeractivation/customersemail/welcomeemailtemplate';
    /**
     *
     */
    const XML_PATH_SENDERDETAIL =
        'customeractivation/customersemail/senderdetail';
    /**
     *
     */
    const XML_PATH_ERRORMESSAGETEXT =
        'customeractivation/message/errormessagetext';
    /**
     *
     */
    const XML_PATH_REGISTRACTIONSUCCESSMESSAGETEXT =
        'customeractivation/message/registration_success_messagetext';

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $groupId
     * @return bool|mixed
     */
    public function getDefaultActivationStatus($groupId)
    {
        $isActiveDefault = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULTACTIVATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $activateByGroupEnabled = $this->scopeConfig->getValue(
            self::XML_PATH_ACTIVATIONFORGROUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$isActiveDefault && $activateByGroupEnabled) {
            if (in_array($groupId, $this->getCustomerActivationGroupIds())) {
                $isActive = false;
            } else {
                $isActive = true;
            }
        } else {
            $isActive = $isActiveDefault;
        }
        return $isActive;
    }

    /**
     * @return mixed
     */
    public function isCustomerActivationByGroup()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ACTIVATIONFORGROUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return array
     */
    public function getCustomerActivationGroupIds()
    {
        return explode(',', (string)$this->scopeConfig->getValue(
            self::XML_PATH_REQUIREACTIVATIONGROUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }

    // check if send new registration email to admin
    /**
     * @return mixed
     */
    public function isSendEmailToAdmin()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ALERTADMIN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    // Get email template for new registration email
    /**
     * @return mixed
     */
    public function getAdminTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMINEMAILTEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    // Get email template for new registration email
    /**
     * @return mixed
     */
    public function getAdminEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMINEMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    // Check if send confirmation email to customer
    /**
     * @return mixed
     */
    public function isSendApprovalEmailToCustomer()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ALERTCUSTOMER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    // Get sender detail for customer approval email
    /**
     * @return array
     */
    public function getSenderdetail()
    {
        $senderType = $this->scopeConfig->getValue(
            self::XML_PATH_SENDERDETAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $name = $this->scopeConfig
            ->getValue('trans_email/ident_' . $senderType . '/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $email = $this->scopeConfig
            ->getValue('trans_email/ident_' . $senderType . '/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $senderData = ['name' => $name, 'email' => $email];
        return $senderData;
    }

    // Get customer approval email template
    /**
     * @return mixed
     */
    public function getcustAprvlEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WELCOMEEMAILTEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    // Get Error message for customer
    /**
     * @return mixed
     */
    public function getErrorMessageForUser()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ERRORMESSAGETEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    // Get Error message for customer
    /**
     * @return mixed
     */
    public function getRegistractionSuccessMessageForUser()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REGISTRACTIONSUCCESSMESSAGETEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
