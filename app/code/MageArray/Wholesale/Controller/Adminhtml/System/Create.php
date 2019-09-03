<?php
namespace MageArray\Wholesale\Controller\Adminhtml\System;

use Magento\Backend\App\Action\Context;

class Create extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Indexer\Model\Processor $processor,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct($context);
        $this->_objectManager = $context->getObjectManager();
        $this->filterManager = $filterManager;
        $this->_storeManager = $storeManager;
        $this->processor = $processor;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_resourceConfig = $resourceConfig;
    }

    public function execute()
    {
        $response = [];
        $data = $this->getRequest()->getPostValue();
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        if ($data['id'] == 1) {
            try {
                $websiteModel = $this->_objectManager
                    ->create('Magento\Store\Model\Website');
                $websiteColl = $this->_objectManager
                    ->create('Magento\Store\Model\Website')->getCollection()
                    ->addFieldToFilter('code', 'b2b');
                if (count($websiteColl) < 1) {
                    $websiteData = [
                        'name' => 'Wholesale Website',
                        'code' => 'b2b'
                    ];

                    $websiteModel->setData($websiteData);
                    $websiteModel->save();
                    $websiteId = $websiteModel->getId();
                    $rootId = $this->_storeManager
                        ->getStore()->getRootCategoryId();
                    $groupData = [
                        'website_id' => $websiteId,
                        'name' => 'Wholesale Store',
                        'root_category_id' => $rootId,
                    ];
                    $groupModel = $this->_objectManager
                        ->create('Magento\Store\Model\Group');
                    $groupModel->setData($groupData);
                    $groupModel->save();
                    $this->_eventManager
                        ->dispatch(
                            'store_group_save',
                            ['group' => $groupModel]
                        );
                    $groupId = $groupModel->getId();
                    $storeData = [
                        'group_id' => $groupId,
                        'website_id' => $websiteId,
                        'name' => 'Wholesale Store View',
                        'code' => 'wholesale_store_view',
                        'is_active' => 1,
                    ];
                    $storeModel = $this->_objectManager
                        ->create('Magento\Store\Model\Store');
                    $storeModel->setData($storeData);
                    $storeModel->save();
                    $this->_eventManager
                        ->dispatch('store_add', ['store' => $storeModel]);

                    $this->_resourceConfig->saveConfig(
                        'web/unsecure/base_url',
                        $baseUrl . 'b2b/',
                        'websites',
                        $websiteId
                    );

                    $this->_resourceConfig->saveConfig(
                        'web/unsecure/base_link_url',
                        $baseUrl . 'b2b/',
                        'websites',
                        $websiteId
                    );

                    $this->_resourceConfig->saveConfig(
                        'web/unsecure/base_static_url',
                        '{{unsecure_base_url}}../pub/static/',
                        'websites',
                        $websiteId
                    );
                    $this->_resourceConfig->saveConfig(
                        'web/unsecure/base_media_url',
                        '{{unsecure_base_url}}../pub/media/',
                        'websites',
                        $websiteId
                    );

                    $this->_resourceConfig->saveConfig(
                        'web/secure/base_url',
                        $baseUrl . 'b2b/',
                        'websites',
                        $websiteId
                    );

                    $this->_resourceConfig->saveConfig(
                        'web/secure/base_link_url',
                        $baseUrl . 'b2b/',
                        'websites',
                        $websiteId
                    );

                    $this->_resourceConfig->saveConfig(
                        'web/secure/base_static_url',
                        '{{secure_base_url}}../pub/static/',
                        'websites',
                        $websiteId
                    );
                    $this->_resourceConfig->saveConfig(
                        'web/secure/base_media_url',
                        '{{secure_base_url}}../pub/media/',
                        'websites',
                        $websiteId
                    );

                    $response['success'] = 1;
                    $this->processor->reindexAll();
                    foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                        $cacheFrontend->clean();
                    }
                } else {
                    $response['success'] = 2;
                }

            } catch (Exeption $e) {
                $response['success'] = 0;
            }
        }

        return $this->_response->representJson(json_encode($response));
    }
}
