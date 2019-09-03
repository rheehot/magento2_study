<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\Utility\Controller\Adminhtml\Theme;

class Import extends \Codazon\ThemeOptions\Controller\Adminhtml\Theme\Import
{    
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');
        $code = str_replace('/','_', $code);
        $code = strtolower($code);
    	$this->pageSetup->install($code);
    	$this->blockSetup->install($code);
    	$this->slideshowSetup->install($code);
    	$this->widgetSetup->install($code);
    	$this->blogCategorySetup->install($code);
    	$this->blogPostSetup->install($code);
    	$this->megaMenuSetup->install($code);
    	
    	$resultRedirect = $this->resultRedirectFactory->create();
		    return $resultRedirect->setPath(
		        'cdzutility/theme/install'
		    );
    }
}
