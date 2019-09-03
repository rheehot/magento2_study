<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Import;

/**
 * Abstract import model
 */
abstract class AbstractImport extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Connect to bd
     */
    protected $_connect;

    /**
     * @var array
     */
    protected $_requiredFields = [];

    /**
     * @var \Magefan\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magefan\Blog\Model\TagFactory
     */
    protected $_tagFactory;

    /**
     * @var \Magefan\Blog\Model\CommentFactory
     */
    protected $_commentFactory;

    /**
     * @var integer
     */
    protected $_importedPostsCount = 0;

    /**
     * @var integer
     */
    protected $_importedCategoriesCount = 0;

    /**
     * @var integer
     */
    protected $_importedTagsCount = 0;

    /**
     * @var integer
     */
    protected $_importedCommentsCount = 0;

    /**
     * @var array
     */
    protected $_skippedPosts = [];

    /**
     * @var array
     */
    protected $_skippedCategories = [];

    /**
     * @var array
     */
    protected $_skippedTags = [];

    /**
     * @var array
     */
    protected $_skippedComments = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magefan\Blog\Model\PostFactory $postFactory,
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory,
     * @param \Magefan\Blog\Model\TagFactory $tagFactory,
     * @param \Magefan\Blog\Model\CommentFactory $commentFactory,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magefan\Blog\Model\CategoryFactory $categoryFactory,
        \Magefan\Blog\Model\TagFactory $tagFactory,
        \Magefan\Blog\Model\CommentFactory $commentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_postFactory = $postFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_tagFactory = $tagFactory;
        $this->_commentFactory = $commentFactory;
        $this->_storeManager = $storeManager;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve import statistic
     * @return \Magento\Framework\DataObject
     */
    public function getImportStatistic()
    {
        return new \Magento\Framework\DataObject([
            'imported_posts_count'      => $this->_importedPostsCount,
            'imported_categories_count' => $this->_importedCategoriesCount,
            'imported_tags_count'       => $this->_importedTagsCount,
            'imported_comments_count'   => $this->_importedCommentsCount,
            'imported_count'            => $this->_importedPostsCount + $this->_importedCategoriesCount + $this->_importedTagsCount + $this->_importedCommentsCount,

            'skipped_posts'             => $this->_skippedPosts,
            'skipped_categories'        => $this->_skippedCategories,
            'skipped_tags'              => $this->_skippedTags,
            'skipped_comments'          => $this->_skippedComments,
            'skipped_count'             => count($this->_skippedPosts) + count($this->_skippedCategories) + count($this->_skippedTags) + count($this->_skippedComments),
        ]);
    }

    /**
     * Prepare import data
     * @param  array $data
     * @return $this
     * @throws \Exception
     */
    public function prepareData($data)
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }

        foreach ($this->_requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \Exception(__('Parameter %1 is required', $field), 1);
            }
        }

        $this->setData($data);

        return $this;
    }

    /**
     * Execute mysql query
     */
    protected function _mysqliQuery($sql)
    {
        $result = mysqli_query($this->_connect, $sql);
        if (!$result) {
            throw new \Exception(
                __('Mysql error: %1.', mysqli_error($this->_connect))
            );
        }

        return $result;
    }

    /**
     * Prepare import identifier
     * @param  string $identifier
     * @return string
     */
    protected function prepareIdentifier($identifier)
    {
        $identifier = urldecode(trim(strtolower($identifier)));

        if (is_numeric($identifier)) {
            $identifier .= 'u' . $identifier;
        }

        if (strlen($identifier) == 1) {
            $identifier .= $identifier;
        }

        return $identifier;
    }
}
