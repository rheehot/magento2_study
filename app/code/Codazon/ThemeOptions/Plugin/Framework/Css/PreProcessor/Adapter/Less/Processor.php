<?php
namespace Codazon\ThemeOptions\Plugin\Framework\Css\PreProcessor\Adapter\Less;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\View\Asset\Source;
use Psr\Log\LoggerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\App\State;
class Processor
{
    public function __construct(
        //\Codazon\ThemeOptions\Framework\App\Config\Initial $initialConfig,
        \Codazon\ThemeOptions\Model\Config\Reader\Store $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory,
        \Magento\Framework\App\ResourceConnection $connection,
        \Codazon\ThemeOptions\Helper\Data $helper,
        LoggerInterface $logger,
        Source $assetSource,
        Temporary $temporaryFile,
        State $appState
    ) {
        $this->state = $state;
        $this->helper = $helper;
        $this->design = $design;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->themeFactory = $themeFactory;
        $this->connection = $connection;
        $this->logger = $logger;
        $this->assetSource = $assetSource;
        $this->temporaryFile = $temporaryFile;
        $this->appState = $appState;
        //print_r($this->config);die;
    }

    public function loadData($path)
    {
        $tmp = explode('/',$path);
        $path = $tmp[0].'/'.$tmp[1].'/'.$tmp[2];
        //$path = 'frontend/'.$asset->getContext()->getThemePath();
        $theme = $this->themeFactory->create()->getThemeByFullPath($path);
        $conn = $this->connection->getConnection();
        $tblConfig = $this->connection->getTableName('design_config_grid_flat');
        if($theme->getThemeId()){
            $q = 'SELECT store_id FROM `'.$tblConfig.'` WHERE theme_theme_id='.$theme->getThemeId() .' ORDER BY store_id ASC';
            $data = $conn->fetchAll($q);
        }else{
            $data = array();
        }
        if(isset($data[0])){
            foreach($data as $row){
                if($row['store_id']){
                    $storeId = $row['store_id'];
                    break;
                }
            }
        }else{
            $storeId = $this->storeManager->getStore()->getId();
        }
        if(empty($storeId)){
            $this->config = array();
            $this->data = array();
        }else{
            $this->config = $this->storeConfig->read($storeId);
            $this->data = array();
        }
    }

    public function getPath($data, $path = null)
    {
        if(!is_array($data)){
            $this->data[$path] = $data;
        }else{
            foreach($data as $key => $value){
                $this->getPath($value, $key);
                /*if($path){
                    $this->getPath($value, $path.'/'.$key);
                }else{
                    $this->getPath($value, $key);
                }*/
            }
        }
    }
    public function aroundProcessContent($subject, $procede, $asset){
        $path = $asset->getPath();
        if($this->appState->isAreaCodeEmulated()){ //is deploying by command
            if (strpos($path, 'styles-l.less') !== false && strpos($path, 'Codazon/fastest') !== false) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
                $rootPath  =  $directory->getRoot();
                
                $variablePath = str_replace('styles-l.less', 'source/_variables.less', $path);
                $variablePath = $rootPath.'/var/view_preprocessed/pub/static/'.$variablePath;
                
                if(file_exists($variablePath)){
                    $content = file_get_contents($variablePath);
                    $content .= $this->appendVariables($path);
                    file_put_contents($variablePath, $content);
                }
                
                $result = $procede($asset);
                return $result;
            }elseif (strpos($path, 'styles-m.less') !== false && strpos($path, 'Codazon/fastest') !== false) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
                $rootPath  =  $directory->getRoot();
                
                $variablePath = str_replace('styles-m.less', 'source/_variables.less', $path);
                $variablePath = $rootPath.'/var/view_preprocessed/pub/static/'.$variablePath;
                
                if(file_exists($variablePath)){
                    $content = file_get_contents($variablePath);
                    $content .= $this->appendVariables($path);
                    file_put_contents($variablePath, $content);
                }
                
                $result = $procede($asset);
                return $result;
            }
        }
        $result = $procede($asset);
        return $result;
    }
    
    public function appendVariables($path){
        $result = '';
        $this->loadData($path);
        $data = $this->config;
        if(isset($this->config['variables'])){
            $data = $this->config['variables'];
        }
        $this->getPath($data);
        //unset($this->data[0]);
        foreach($this->data as $key => $value){
            //$var = str_replace('/','-',$key);
            //$result .= '@'.$key.":~'".$value."'; ";
            if (strpos($value, '#') !== false) {
                $result .= '@'.$key.':'.$value.'; ';
            } elseif(strpos($value, 'rgba(') !== false){
                $result .= '@'.$key.':'.$value.'; ';
            }else{
                
                if(preg_match("/background_file/",$key))
                {
                    if($value){
                        $background_url = $this->helper->getMediaUrl() .'codazon/themeoptions/background/'. $value;
                        $result .= '@'.$key.":~'".$background_url."'; ";
                    }else{
                        $value = '../images/transparent.png';
                        $result .= '@'.$key.":~'".$value."'; ";
                    }
                }
                else
                    $result .= '@'.$key.":~'".$value."'; ";
            }
        }
        return $result;
    }
}
