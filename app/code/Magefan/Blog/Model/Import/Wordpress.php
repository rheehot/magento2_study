<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Import;

/**
 * Wordpress import model
 */
class Wordpress extends AbstractImport
{
    protected $_requiredFields = ['dbname', 'uname', 'dbhost'];

    public function execute()
    {
        $con = $this->_connect = mysqli_connect(
            $this->getData('dbhost'),
            $this->getData('uname'),
            $this->getData('pwd'),
            $this->getData('dbname')
        );

        if (mysqli_connect_errno()) {
            throw new \Exception("Failed connect to wordpress database", 1);
        }

        mysqli_set_charset($con, "utf8");

        $_pref = mysqli_real_escape_string($con, $this->getData('prefix'));

        $categories = [];
        $oldCategories = [];

        /* Import categories */
        $sql = 'SELECT
                    t.term_id as old_id,
                    t.name as title,
                    t.slug as identifier,
                    tt.parent as parent_id
                FROM '.$_pref.'terms t
                LEFT JOIN '.$_pref.'term_taxonomy tt on t.term_id = tt.term_id
                WHERE tt.taxonomy = "category" AND t.slug <> "uncategorized"';

        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
            /* Prepare category data */
            foreach (['title', 'identifier'] as $key) {
                $data[$key] = mb_convert_encoding($data[$key], 'HTML-ENTITIES', 'UTF-8');
            }

            $data['store_ids'] = [$this->getStoreId()];
            $data['is_active'] = 1;
            $data['position'] = 0;
            $data['path'] = 0;
            $data['identifier'] = $this->prepareIdentifier($data['identifier']);

            $category = $this->_categoryFactory->create();
            try {
                /* Initial saving */
                $category->setData($data)->save();
                $this->_importedCategoriesCount++;
                $categories[$category->getId()] = $category;
                $oldCategories[$category->getOldId()] = $category;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                unset($category);
                $this->_skippedCategories[] = $data['title'];
                $this->_logger->addDebug('Blog Category Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }

        /* Reindexing parent categories */
        foreach ($categories as $ct) {
            if ($oldParentId = $ct->getData('parent_id')) {
                if (isset($oldCategories[$oldParentId])) {
                    $ct->setPath(
                        $parentId = $oldCategories[$oldParentId]->getId()
                    );
                }
            }
        }

        for ($i = 0; $i < 4; $i++) {
            $changed = false;
            foreach ($categories as $ct) {
                if ($ct->getPath()) {
                    $parentId = explode('/', $ct->getPath())[0];
                    $pt = $categories[$parentId];
                    if ($pt->getPath()) {
                        $ct->setPath($pt->getPath() . '/'. $ct->getPath());
                        $changed = true;
                    }
                }
            }

            if (!$changed) {
                break;
            }
        }
        /* end*/

        foreach ($categories as $ct) {
            /* Final saving */
            $ct->save();
        }

        /* Import tags */
        $tags = [];
        $oldTags = [];

        $sql = 'SELECT
                    t.term_id as old_id,
                    t.name as title,
                    t.slug as identifier,
                    tt.parent as parent_id
                FROM '.$_pref.'terms t
                LEFT JOIN '.$_pref.'term_taxonomy tt on t.term_id = tt.term_id
                WHERE tt.taxonomy = "post_tag" AND t.slug <> "uncategorized"';

        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
            /* Prepare tag data */
            foreach (['title', 'identifier'] as $key) {
                $data[$key] = mb_convert_encoding($data[$key], 'HTML-ENTITIES', 'UTF-8');
            }

            if ($data['title']{0} == '?') {
                /* fix for ???? titles */
                $data['title'] = $data['identifier'];
            }

            $data['identifier'] = $this->prepareIdentifier($data['identifier']);

            $tag = $this->_tagFactory->create();
            try {
                /* Initial saving */
                $tag->setData($data)->save();
                $this->_importedTagsCount++;
                $tags[$tag->getId()] = $tag;
                $oldTags[$tag->getOldId()] = $tag;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                unset($tag);
                $this->_skippedTags[] = $data['title'];
                $this->_logger->addDebug('Blog Tag Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }

        /* Import posts */
        $sql = 'SELECT * FROM '.$_pref.'posts WHERE `post_type` = "post"';
        $result = $this->_mysqliQuery($sql);

        while ($data = mysqli_fetch_assoc($result)) {
            /* find post categories*/
            $postCategories = [];

            $sql = 'SELECT tt.term_id as term_id FROM '.$_pref.'term_relationships tr
                    LEFT JOIN '.$_pref.'term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.`object_id` = "'.$data['ID'].'" AND tt.taxonomy = "category"';

            $result2 = $this->_mysqliQuery($sql);
            while ($data2 = mysqli_fetch_assoc($result2)) {
                $oldTermId = $data2['term_id'];
                if (isset($oldCategories[$oldTermId])) {
                    $postCategories[] = $oldCategories[$oldTermId]->getId();
                }
            }

            /* find post tags*/
            $postTags = [];

            $sql = 'SELECT tt.term_id as term_id FROM '.$_pref.'term_relationships tr
                    LEFT JOIN '.$_pref.'term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.`object_id` = "'.$data['ID'].'" AND tt.taxonomy = "post_tag"';

            $result2 = $this->_mysqliQuery($sql);
            while ($data2 = mysqli_fetch_assoc($result2)) {
                $oldTermId = $data2['term_id'];
                if (isset($oldTags[$oldTermId])) {
                    $postTags[] = $oldTags[$oldTermId]->getId();
                }
            }

            $data['featured_img'] = '';

            $sql = 'SELECT wm2.meta_value as featured_img
                FROM
                    '.$_pref.'posts p1
                LEFT JOIN
                    '.$_pref.'postmeta wm1
                    ON (
                        wm1.post_id = p1.id
                        AND wm1.meta_value IS NOT NULL
                        AND wm1.meta_key = "_thumbnail_id"
                    )
                LEFT JOIN
                    '.$_pref.'postmeta wm2
                    ON (
                        wm1.meta_value = wm2.post_id
                        AND wm2.meta_key = "_wp_attached_file"
                        AND wm2.meta_value IS NOT NULL
                    )
                WHERE
                    p1.ID="'.$data['ID'].'"
                    AND p1.post_type="post"
                ORDER BY
                    p1.post_date DESC';

            $result2 = $this->_mysqliQuery($sql);
            if ($data2 = mysqli_fetch_assoc($result2)) {
                if ($data2['featured_img']) {
                    $data['featured_img'] = \Magefan\Blog\Model\Post::BASE_MEDIA_PATH . '/' . $data2['featured_img'];
                }
            }

            /* Prepare post data */
            foreach (['post_title', 'post_name', 'post_content'] as $key) {
                $data[$key] = mb_convert_encoding($data[$key], 'HTML-ENTITIES', 'UTF-8');
            }

            $creationTime = strtotime($data['post_date_gmt']);

            $content = $data['post_content'];
            $content = str_replace('<!--more-->', '<!-- pagebreak -->', $content);

            $content = preg_replace(
                '/((http:\/\/|https:\/\/|\/\/)(.*)|(\s|"|\')|(\/[\d\w_\-\.]*))\/wp-content\/uploads(.*)((\.jpg|\.jpeg|\.gif|\.png|\.tiff|\.tif|\.svg)|(\s|"|\'))/Ui',
                '$4{{media url="magefan_blog$6$8"}}$9',
                $content
            );
            $wordpressPostId = $data['ID'];
            $data = [
                'store_ids' => [$this->getStoreId()],
                'title' => $data['post_title'],
                'meta_keywords' => '',
                'meta_description' => '',
                'identifier' => $data['post_name'],
                'content_heading' => '',
                'content' => $content,
                'creation_time' => $creationTime,
                'update_time' => strtotime($data['post_modified_gmt']),
                'publish_time' => $creationTime,
                'is_active' => (int)($data['post_status'] == 'publish'),
                'categories' => $postCategories,
                'tags' => $postTags,
                'featured_img' => $data['featured_img'],
            ];

            $data['identifier'] = $this->prepareIdentifier($data['identifier']);

            $post = $this->_postFactory->create();
            try {
                /* Post saving */
                $post->setData($data)->save();

                /* find post comment s*/
                $sql = 'SELECT * FROM '.$_pref.'comments WHERE `comment_approved`=1 AND `comment_post_ID` = ' . $wordpressPostId;
                $resultComments = $this->_mysqliQuery($sql);
                $commentParents = [];

                while ($comments = mysqli_fetch_assoc($resultComments)) {
                    $commentParentId = 0;
                    if (!($comments['comment_parent'] == 0) && isset($commentParents[$comments["comment_parent"]])) {
                        $commentParentId = $commentParents[$comments["comment_parent"]];
                    }
                    $commentData = [
                        'parent_id' => $commentParentId,
                        'post_id' => $post->getPostId(),
                        'status' => \Magefan\Blog\Model\Config\Source\CommentStatus::APPROVED,
                        'author_type' => \Magefan\Blog\Model\Config\Source\AuthorType::GUEST,
                        'author_nickname' => $comments['comment_author'],
                        'author_email' => $comments['comment_author_email'],
                        'text' => $comments['comment_content'],
                        'creation_time' => $comments['comment_date'],
                    ];

                    if (!$commentData['text']) {
                        continue;
                    }

                    $comment = $this->_commentFactory->create($commentData);

                    try {
                        /* Initial saving */
                        $comment->setData($commentData)->save();
                        $this->_importedCommentsCount++;
                        $commentParents[$comments["comment_ID"]] = $comment->getCommentId();
                    } catch (\Exception $e) {
                        $this->_skippedComments[] = $commentData['title'];
                        unset($comment);
                    }
                }
                $this->_importedPostsCount++;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_skippedPosts[] = $data['title'];
                $this->_logger->addDebug('Blog Post Import [' . $data['title'] . ']: '. $e->getMessage());
            }

            unset($post);
        }
        /* end */

        mysqli_close($con);
    }
}
