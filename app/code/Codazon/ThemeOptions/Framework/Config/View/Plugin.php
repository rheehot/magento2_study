<?php
namespace Codazon\ThemeOptions\Framework\Config\View;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Plugin
{
    public function __construct(
        \Codazon\ThemeOptions\Helper\Data $helper,
        \Magento\Framework\App\State $state
    ) {
        $this->_helper = $helper;
        $this->_state = $state;
    }
    
    public function aroundGetVarValue($subject, $procede, $module, $var)
    {
        if($module == 'Magento_Catalog'){
            if($var == 'gallery/navdir'){
                return $this->_helper->getConfig('general_section/product_view/moreview_thumb_style');
            }
            elseif($var == 'gallery/allowfullscreen'){
                if($this->_helper->getConfig('general_section/product_view/disable_product_zoom')){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        $result = $procede($module, $var);
        return $result;
    }

    public function afterRead($subject, $result)
    {
        if($this->_helper->getConfig('general_section/category_view/image_width')){
            $result['media']['Magento_Catalog']['images']['category_page_grid']['width'] = (int)$this->_helper->getConfig('general_section/category_view/image_width');
        }
        if($this->_helper->getConfig('general_section/category_view/image_height')){
            $result['media']['Magento_Catalog']['images']['category_page_grid']['height'] = (int)$this->_helper->getConfig('general_section/category_view/image_height');
        }
        if($this->_helper->getConfig('general_section/category_view/image_width')){
            $result['media']['Magento_Catalog']['images']['category_page_grid_hover']['width'] = (int)$this->_helper->getConfig('general_section/category_view/image_width');
        }
        if($this->_helper->getConfig('general_section/category_view/image_height')){
            $result['media']['Magento_Catalog']['images']['category_page_grid_hover']['height'] = (int)$this->_helper->getConfig('general_section/category_view/image_height');
        }
        if($this->_helper->getConfig('general_section/category_view/image_width')){
            $result['media']['Magento_Catalog']['images']['category_page_list']['width'] = (int)$this->_helper->getConfig('general_section/category_view/image_width');
        }
        if($this->_helper->getConfig('general_section/category_view/image_height')){
            $result['media']['Magento_Catalog']['images']['category_page_list']['height'] = (int)$this->_helper->getConfig('general_section/category_view/image_height');
        }
        if($this->_helper->getConfig('general_section/category_view/image_width')){
            $result['media']['Magento_Catalog']['images']['category_page_list_hover']['width'] = (int)$this->_helper->getConfig('general_section/category_view/image_width');
        }
        if($this->_helper->getConfig('general_section/category_view/image_height')){
            $result['media']['Magento_Catalog']['images']['category_page_list_hover']['height'] = (int)$this->_helper->getConfig('general_section/category_view/image_height');
        }
        if($this->_helper->getConfig('general_section/product_view/moreview_image_width')){
            $result['media']['Magento_Catalog']['images']['product_page_image_small']['width'] = (int)$this->_helper->getConfig('general_section/product_view/moreview_image_width');
        }
        if($this->_helper->getConfig('general_section/product_view/moreview_image_height')){
            $result['media']['Magento_Catalog']['images']['product_page_image_small']['height'] = (int)$this->_helper->getConfig('general_section/product_view/moreview_image_height');
        }
        if($this->_helper->getConfig('general_section/product_view/base_image_width')){
            $result['media']['Magento_Catalog']['images']['product_page_image_large']['width'] = (int)$this->_helper->getConfig('general_section/product_view/base_image_width');
        }
        if($this->_helper->getConfig('general_section/product_view/base_image_height')){
            $result['media']['Magento_Catalog']['images']['product_page_image_large']['height'] = (int)$this->_helper->getConfig('general_section/product_view/base_image_height');
        }
        return $result;
    }
}