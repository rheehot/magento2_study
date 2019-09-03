<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\Utility\Controller\Adminhtml\Config;
class Edit extends \Codazon\ThemeOptions\Controller\Adminhtml\Config\Edit
{
    /**
     * Edit configuration section
     *
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        $current = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');
        $code = $this->getRequest()->getParam('code');
        $theme = $this->getRequest()->getParam('theme_id');
        
		$currentThemeId = $this->getCurrentThemeId();
		if($theme != $currentThemeId){
			$resultRedirect = $this->resultRedirectFactory->create();
		    return $resultRedirect->setPath(
		        'cdzutility/config/edit',
		        [
		        	'theme_id' => $currentThemeId,
		        	'code'	   => $code,
		        	'section'  => $current,
		            'website'  => $website,
		            'store'    => $store
		        ]
		    );
		}
        $section = $this->_configStructure->getElement($current);
        $resultPage = $this->resultPageFactory->create();

        if($theme){
		    $themeModel = $this->_objectManager->get('\Magento\Theme\Model\Theme');
		    $themeModel->load($theme);
		    if(strpos($themeModel->getThemePath(), 'Codazon')!==true){
			    $resultPage->getConfig()->getTitle()->prepend(__($themeModel->getThemeTitle()));		    
			    $layout = $resultPage->getLayout();
			    $layout->addBlock('Codazon\ThemeOptions\Block\Adminhtml\Config\Tabs','adminhtml.system.config.tabs','left');
                if($this->isDeveloperMode){
                    $layout->addBlock('Codazon\ThemeOptions\Block\Adminhtml\Config\Edit','adminhtml.system.config.edit','content');
                }else{
                    $block = $this->_objectManager->create('Magento\Framework\View\Element\Text');
                    $block->setText(__('Please change to developer mode to use this function.'));
                    $layout->addBlock($block,'adminhtml.system.config.edit','content');
                }
			}
        }
        
        $resultPage->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo([$current]);
        $resultPage->addBreadcrumb(__('System'), __('System'), $this->getUrl('*\/system'));
        
        return $resultPage;
    }
}
