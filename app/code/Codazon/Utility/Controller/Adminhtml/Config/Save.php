<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\Utility\Controller\Adminhtml\Config;

class Save extends \Codazon\ThemeOptions\Controller\Adminhtml\Config\Save
{
    /**
     * Save configuration
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $section = $this->getRequest()->getParam('section');
            $website = $this->getRequest()->getParam('website');
            $theme = $this->getRequest()->getParam('theme_id');
            $store = $this->getRequest()->getParam('store');

            $configData = [
                'section' => $section,
                'store'	=> $store,
                'website' => $website,
                'theme' => $theme,
                'groups' => $this->_getGroupsForSave(),
            ];                        
            /** @var \Magento\Config\Model\Config $configModel  */
            $configModel = $this->_configFactory->create(['data' => $configData]);
            $configModel->save();
            $typographyList = array('css','fonts','page','header','menu','body','footer','buttons');

            //clear pub static file
            if($section == 'variables'){
                if($this->isDeveloperMode){
                    $this->_objectManager->get('Magento\Framework\App\State\CleanupFiles')->clearMaterializedViewFiles();
                    $this->_eventManager->dispatch('clean_static_files_cache_after');
                    $this->messageManager->addSuccess(__('The static files cache has been cleaned.'));
                }else{
                    $this->messageManager->addSuccess(__(' Please change to developer mode to use this function.'));
                }
		        
		    }

			$this->_cache->clean();
            $this->messageManager->addSuccess(__('You saved the configuration.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'cdzutility/config/edit',
            [
            	'theme_id' => $theme,
                '_current' => ['section', 'website', 'store', 'code'],
                '_nosid' => true
            ]
        );
    }
}
