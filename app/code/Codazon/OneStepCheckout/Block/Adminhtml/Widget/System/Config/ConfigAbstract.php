<?php

namespace Codazon\OneStepCheckout\Block\Adminhtml\Widget\System\Config;

class ConfigAbstract extends \Magento\Backend\Block\Template
{

    protected $_scopeId = 0;

    protected $_scope = 'default';

    protected $fileSystem;

    protected $_dataConfigCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $dataConfigCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->fileSystem = $context->getFilesystem();
        $this->_dataConfigCollectionFactory = $dataConfigCollectionFactory;
    }

    public function getFileSystem() {
        return $this->fileSystem;
    }
    
    public function setScopeId($scopeId)
    {
        $this->_scopeId = $scopeId;

        return $this;
    }

    public function setScope($scope)
    {
        $this->_scope = $scope;

        return $this;
    }

    public function getScope()
    {
        return $this->_scope;
    }

    public function getScopeId()
    {
        return $this->_scopeId;
    }

    protected function _construct()
    {
        parent::_construct();
        $storeCode = $this->getRequest()->getParam('store');
        $website = $this->getRequest()->getParam('website');
        if ($storeCode) {
            $scopeId = $this->_storeManager->getStore($storeCode)->getId();
            $scope = 'stores';
        } elseif ($website) {
            $scope = 'websites';
            $scopeId = $this->_storeManager->getWebsite($website)->getId();
        } else {
            $scope = 'default';
            $scopeId = 0;
        }

        $this->setScopeId($scopeId);
        $this->setScope($scope);

        return $this;
    }

}