<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Setup\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Block
 */
class Block
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var Block\Converter
     */
    protected $converter;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param Block\Converter $converter
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        Block\Converter $converter,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->blockFactory = $blockFactory;
        $this->converter = $converter;
        $this->categoryRepository = $categoryRepository;
        $this->blockCollectionFactory = $blockCollectionFactory;
    }
    
    public function export($code)
    {
        $path = dirname(dirname(__DIR__)).'/fixtures/'.$code;
        $code = str_replace('fastest_','fastest-',$code);
        $list = array (
            array('title', 'identifier', 'content')
        );
        
        $this->blockCollection = $this->blockCollectionFactory->create();
        $this->blockCollection->addFieldToSelect('*');
        $this->blockCollection->addFieldToFilter('identifier',array('like' => '%'.$code.'%'));
        foreach($this->blockCollection as $block){
            $data = [];
            foreach($list[0] as $attribute){
                $data[] = $block->getData($attribute);
                if($attribute == 'identifier'){
                    echo 'id: '.$block->getData($attribute).'<br/>';
                }
            }
            $list[] = $data;
        }

        $fp = fopen($path.'/blocks.csv', 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
        echo 'export block finish'.'<br/>';
    }

    public function converConditionTo21($string){
        $content = $string;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $serializeConditionHelper = $objectManager->get('\Codazon\ThemeOptions\Helper\SerializedConditions');
        $jsonConditionHelper = $objectManager->get('\Codazon\ThemeOptions\Helper\JsonConditions');
        $needReplace = array();
        $constructions = array();

        $pattern = '/conditions_encoded=\\"(.*?)\\"/';
        if (preg_match_all($pattern, $content, $constructions, PREG_SET_ORDER)) {
            foreach($constructions as $index => $construction) {
                $needReplace[] = $construction[1];
            }
        }

        $needReplace = array_unique($needReplace);
        
        foreach ($needReplace as $replace) {
            $condition = $serializeConditionHelper->encode($jsonConditionHelper->decode($replace));
            $string = str_replace($replace, $condition, $string);
        }
        return $string;
    }

    public function install($code)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();
        

        $fileName = dirname(dirname(__DIR__)).'/fixtures/'.$code.'/blocks.csv';
        //$fileName = $this->fixtureManager->getFixture($fileName);
        if (!file_exists($fileName)) {
            return;
        }

        $rows = $this->csvReader->getData($fileName);
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $row = $data;
            if (version_compare($version, '2.2.0', '<') || version_compare($version, '2.2.0-dev', '<')) {
                $row['content'] = $this->converConditionTo21($row['content']);
            }

            $blockCollection = $this->blockCollectionFactory->create();
            $blockCollection->addFilter('identifier', $row['identifier']);
            if ($blockCollection->count() > 0) {
                continue;
            }
            
            $data = $this->converter->convertRow($row);

            

            $cmsBlock = $this->saveCmsBlock($data['block']);
            $cmsBlock->unsetData();
        }
    }

    /**
     * @param array $data
     * @return \Magento\Cms\Model\Block
     */
    protected function saveCmsBlock($data)
    {
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->getResource()->load($cmsBlock, $data['identifier']);
        if (!$cmsBlock->getData()) {
            $cmsBlock->setData($data);
        } else {
            $cmsBlock->addData($data);
        }
        $cmsBlock->setStores([\Magento\Store\Model\Store::DEFAULT_STORE_ID]);
        $cmsBlock->setIsActive(1);
        $cmsBlock->save();
        return $cmsBlock;
    }

    /**
     * @param string $blockId
     * @param string $categoryId
     * @return void
     */
    protected function setCategoryLandingPage($blockId, $categoryId)
    {
        $categoryCms = [
            'landing_page' => $blockId,
            'display_mode' => 'PRODUCTS_AND_PAGE',
        ];
        if (!empty($categoryId)) {
            $category = $this->categoryRepository->get($categoryId);
            $category->setData($categoryCms);
            $this->categoryRepository->save($categoryId);
        }
    }
}
