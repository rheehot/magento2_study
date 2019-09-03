<?php


namespace Codazon\Utility\Controller\Adminhtml\Theme;

class Active extends \Codazon\ThemeOptions\Controller\Adminhtml\Theme\Save
{

    public function execute()
    {
        $params = array();
        $themeConfigOb = $this->_themeConfigFactory->create();
        $path = \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID;
        $config = $this->_configFactory->create();
        $this->theme_id = $this->getRequest()->getParam('theme_id');
        $this->section = 'general_section';
        $this->website = $this->getRequest()->getParam('website');
        $this->store = $this->getRequest()->getParam('store');
        $this->code = $this->getRequest()->getParam('code');
        
        $params['code'] = $this->code;
        
        if($this->website)
        {
            $params['website'] = $this->website;
        }
        else if($this->store)
        {
            $params['store'] = $this->store;
        }
        $config->setData($params);
        $config->setDataByPath($path,$this->theme_id);
        $config->save();
        
        $cmsHomePage = $themeConfigOb->getValue('cms_home_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->store);
        $config->setDataByPath('web/default/cms_home_page',$cmsHomePage);
        $config->save();
        $this->indexerRegistry->get(\Magento\Theme\Model\Data\Design\Config::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'cdzutility/config/edit',
            [
                'theme_id' => $this->theme_id,
                'code'     => $this->code,
                'section'  => $this->section,
                'website'  => $this->website,
                'store'    => $this->store
            ]
        );
    }
}
