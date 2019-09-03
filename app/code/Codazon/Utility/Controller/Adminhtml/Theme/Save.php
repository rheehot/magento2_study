<?php


namespace Codazon\Utility\Controller\Adminhtml\Theme;

class Save extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvier
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->themeProvier = $themeProvier;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $rootPath  =  $directory->getRoot();
        $designPath = $rootPath.'/app/design/frontend/';

        $package = $_REQUEST['package'];
        $theme = $_REQUEST['theme'];

        if (!file_exists($designPath.$package)) {
            mkdir($designPath.$package, 0777, true);
        }
        if (!file_exists($designPath.$package.'/'.$theme)) {
            mkdir($designPath.$package.'/'.$theme, 0777, true);
        }
        $parent = $this->themeProvier->getThemeById($_REQUEST['parent']);
        $parentPath = $parent->getThemePath();
        $copyPath = str_replace("fastest_","fastest/",$parentPath);
        $this->recurse_copy($designPath.$copyPath, $designPath.$package.'/'.$theme);

        //=== create theme.xml ===
        $xml = simplexml_load_file($designPath.$package.'/'.$theme.'/theme.xml');
        $xml->title = "$package - $theme";
        $xml->parent = $parentPath;
        $xml->asXml($designPath.$package.'/'.$theme.'/theme.xml');

        //=== create registration.php
        if($package != 'Codazon/fastest'){
            $code = "<?php
                \Magento\Framework\Component\ComponentRegistrar::register(
                    \Magento\Framework\Component\ComponentRegistrar::THEME,
                    'frontend/$package/$theme',
                    __DIR__
                );";
            file_put_contents($designPath.$package.'/'.$theme.'/registration.php', $code);
        }

        //=== clone block, widget 
        //$package = str_replace('Codazon/', '', $package);
        $fixturePath = $rootPath.'/app/code/Codazon/ThemeOptions/fixtures/';
        $src = $fixturePath.str_replace('Codazon/','',$parent->getThemePath());
        $dsc = $fixturePath.strtolower("{$package}_{$theme}");
        if($package == 'Codazon/fastest'){
            $dsc = str_replace('codazon/', '', $dsc);
        }
        if (!file_exists($dsc)) {
            mkdir($dsc, 0777, true);
        }
        $this->recurse_copy($src, $dsc);

        $find1 = str_replace('Codazon/','',$parent->getThemePath());
        $find1 = str_replace('_','-', $find1);
        $find2 = str_replace('-',' - ', $find1);
        $find2 = ucwords($find2);
        $replace1 = strtolower("{$package}-{$theme}");
        $replace1 = str_replace('codazon/', '', $replace1);
        $replace2 = ucwords("{$package} - {$theme}");
        $replace2 = str_replace('Codazon/', '', $replace2);

        $content = file_get_contents($dsc.'/blocks.csv');
        $content = str_replace($find1, $replace1, $content);
        $content = str_replace($find2, $replace2, $content);
        file_put_contents($dsc.'/blocks.csv', $content);

        $find3 = 'frontend/'.$parent->getThemePath();
        if($package == 'Codazon/fastest'){
            $replace3 = "frontend/{$package}_{$theme}";
        }else{
            $replace3 = "frontend/{$package}/{$theme}";
        }
        $content = file_get_contents($dsc.'/widgets.csv');
        $content = str_replace($find1, $replace1, $content);
        $content = str_replace($find2, $replace2, $content);
        $content = str_replace($find3, $replace3, $content);
        file_put_contents($dsc.'/widgets.csv', $content);

        $find3 = str_replace('Codazon/','',$parent->getThemePath());
        $find3 = str_replace('_',' ', $find3);
        $replace3 = "{$package} {$theme}";
        $replace3 = str_replace('Codazon/', '', $replace3);
        $content = file_get_contents($dsc.'/slideshows.csv');
        $content = str_replace($find1, $replace1, $content);
        $content = str_replace($find2, $replace2, $content);
        $content = str_replace($find3, $replace3, $content);
        file_put_contents($dsc.'/slideshows.csv', $content);

        $find3 = str_replace('Codazon/','',$parent->getThemePath());
        $find3 = str_replace('_',' ', $find3);
        $replace3 = "{$package} {$theme}";
        $replace3 = str_replace('Codazon/', '', $replace3);
        $content = file_get_contents($dsc.'/megamenus.csv');
        $content = str_replace($find1, $replace1, $content);
        $content = str_replace($find2, $replace2, $content);
        $content = str_replace($find3, $replace3, $content);
        file_put_contents($dsc.'/megamenus.csv', $content);

        $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath(
                'cdzutility/theme/index'
            );
    }

    public function recurse_copy($src,$dst)
    { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { 
                    copy($src . '/' . $file,$dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
    } 
}
