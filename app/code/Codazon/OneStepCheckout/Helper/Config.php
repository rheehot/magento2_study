<?php

namespace Codazon\OneStepCheckout\Helper;
use Magento\Customer\Model\AccountManagement;

class Config extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_customerSession;
	
    protected $_regionCollection;
	
    protected $_directoryHelper;

    protected $_subscriberFactory;

    protected $_moduleManager;

    protected $_localCountry;

    protected $_objectManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Config\Model\Config\Source\Locale\Country $localCountry,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_regionCollection = $regionCollection;
        $this->_directoryHelper = $directoryHelper;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_moduleManager = $context->getModuleManager();
        $this->_localCountry = $localCountry;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    const SECTION_CONFIG_ONESTEPCHECKOUT = 'opccodazon';
    
    public function getOneStepConfig($relativePath) {
        return $this->scopeConfig->getValue(self::SECTION_CONFIG_ONESTEPCHECKOUT . '/' . $relativePath);
    }

    public function isEnabledOneStep() {
        return $this->getOneStepConfig('general/active');
    }
	
    public function getFullRequest()
    {
        $routeName = $this->_getRequest()->getRouteName();
        $controllerName = $this->_getRequest()->getControllerName();
        $actionName = $this->_getRequest()->getActionName();
        return $routeName.'_'.$controllerName.'_'.$actionName;
    }
    
    public function getAddressFieldsConfig()
    {
        $configs = array();
        $configs['twoFields'] = array();
        $configs['oneFields'] = array('street.0','street.1','street.2','street.3');
        $configs['lastFields'] = array();
        $configs['position'] = array();
        for($position = 0; $position < 20; $position++){
            $prePos = $position - 1;
            $currentPos = $position;
            $nextPos = $position + 1;
            $prepath = 'field_position_management/row_'.$prePos;
            $path = 'field_position_management/row_'.$currentPos;
            $nextpath = 'field_position_management/row_'.$nextPos;
            $preField = $this->getOneStepConfig($prepath);
            $currentField = $this->getOneStepConfig($path);
            $nextField = $this->getOneStepConfig($nextpath);
            if($currentField != '0'){
                if($currentField == 'street'){
                    $configs['position']['street'] = $currentPos;
                    $configs['position']['street.0'] = $currentPos;
                    $configs['position']['street.1'] = $currentPos;
                    $configs['position']['street.2'] = $currentPos;
                    $configs['position']['street.3'] = $currentPos;
                }elseif($currentField == 'region_id'){
                    $configs['position']['region_id'] = $currentPos;
                    $configs['position']['region'] = $currentPos;
                }else{
                    $configs['position'][$currentField] = $currentPos;
                }
            }
            if($currentField != 'street' && $currentField != '0'){
                if( $currentPos%2 == 0){
                    if($currentField != '0' && $nextField == '0'){
                        $configs['oneFields'][] = $currentField;
                        if($currentField == 'region_id'){
                            $configs['oneFields'][] = 'region';
                        }
                    }else{
                        $configs['twoFields'][] = $currentField;
                        if($currentField == 'region_id'){
                            $configs['twoFields'][] = 'region';
                        }
                    }
                }else{
                    if($currentField != '0' && $preField == '0'){
                        $configs['oneFields'][] = $currentField;
                        if($currentField == 'region_id'){
                            $configs['oneFields'][] = 'region';
                        }
                    }else{
                        $configs['twoFields'][] = $currentField;
                        $configs['lastFields'][] = $currentField;
                        if($currentField == 'region_id'){
                            $configs['twoFields'][] = 'region';
                            $configs['lastFields'][] = 'region';
                        }
                    }
                }
            }
        }
        return $configs;
    }
  
    public function getAddressFieldsJsonConfig()
    {
        return \Zend_Json::encode($this->getAddressFieldsConfig());
    }
    
    public function getFieldSortOrder($fieldKey){
        $config = $this->getAddressFieldsConfig();
        if(isset($config['position']) && isset($config['position'][$fieldKey])){
            return $config['position'][$fieldKey];
        }
        return false;
    }

    public function isLogin() {
        return $this->_customerSession->isLoggedIn();
    }

    public function isAllowCountries($countryCode) {
        $allowCountries = explode(',', (string)$this->scopeConfig->getValue('general/country/allow',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if (!empty($allowCountries)) {
            if (!in_array($countryCode, $allowCountries)) {
                return false;
            }
        }
        return true;
    }
    
    public function getMinimumPasswordLength()
    {
        return $this->scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    public function getRequiredCharacterClassesNumber()
    {
        return $this->scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }

    public function canShowNewsletter()
    {
        $isShowNewsletter = $this->getOneStepConfig('general/show_newsletter');

        if ($isShowNewsletter && !$this->isSignUpNewsletter()) {
            return true;
        } else {
            return false;
        }
    }
	
	public function canTermsAndConditions()
    {
        $terms_and_conditions = $this->getOneStepConfig('terms_and_conditions/terms_enable');

        if ($terms_and_conditions) {
            return true;
        } else {
            return false;
        }
    }
	
	public function getTermsAndConWarning()
    {
        return $this->getOneStepConfig('terms_and_conditions/terms_warning');
    }
	
	public function getTermsAndConWarningContent()
    {
        return $this->getOneStepConfig('terms_and_conditions/terms_warning_content');
    }
	
	public function getTermsAndConTermsContent()
    {
        return $this->getOneStepConfig('terms_and_conditions/terms_content');
    }
	
	public function getTermsAndConTitle()
    {
        return $this->getOneStepConfig('terms_and_conditions/terms_text');
    }

    public function isSignUpNewsletter()
    {
        $isLogin = $this->_customerSession->isLoggedIn();
        if ($isLogin) {
            $customer = $this->_customerSession->getCustomer();
            if (isset($customer))
                $customerNewsletter = $this->_subscriberFactory->create()->loadByEmail($customer->getEmail());
            if (isset($customerNewsletter) && $customerNewsletter->getId() != null &&
                $customerNewsletter->getData('subscriber_status') == 1
            ) {
                return true;
            }
        }
        return false;
    }

    public function getMagentoVersion() {
        $productMetadata = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        return $productMetadata->getVersion();
    }

    public function canShowPasswordMeterValidate() {
        if(version_compare($this->getMagentoVersion(), '2.1.0') >= 0) {
            return true;
        } else {
            return false;
        }
    }
}