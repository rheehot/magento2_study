<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Lookbookpro\Block\Widget;

class FeaturedLook extends \Codazon\Lookbookpro\Block\Item\AbstractItem implements \Magento\Widget\Block\BlockInterface
{
    protected $_lookCollection;
    
    protected $_template = 'widget/featured-look/featured-look-style-01.phtml';
    
    public function _getCollection()
    {
        if ($this->_lookCollection === null) {
            $lookIds = $this->getData('look_ids');
            $lookbookIds = $this->getData('lookbook_ids');
            if ($lookIds) {
                if (is_string($lookIds)) {
                    $lookIds = explode(',', $lookIds);
                }
            }
            if ($lookbookIds) {
                if (is_array($lookbookIds)) {
                    $lookbookIds = implode(',', $lookbookIds);
                }
            }
            $this->_lookCollection = $this->_objectManager->get('\Codazon\Lookbookpro\Model\LookbookItem')->getCollection();
            $this->_lookCollection->addAttributeToSelect('*');
            if ($lookIds) {
                $this->_lookCollection->addFieldToFilter('entity_id', ['in' => $lookIds]);
            }
            if ($lookbookIds) {
                $this->_lookCollection->getSelect()->joinLeft(
                    ['ccl' => $this->_lookCollection->getTable('cdzlookbook_item_group')],
                    'e.entity_id = ccl.item_id',
                    ['lookbook_id', 'position']
                )->group('e.entity_id')
                ->where('ccl.lookbook_id in ('. $lookbookIds .')');
            }
            
        }
        return $this->_lookCollection;
    }
    
    public function getTemplate()
    {
        if ($this->getData('custom_template') != '') {
            return $this->getData('custom_template');
        } else {
            if (parent::getTemplate()) {
                return parent::getTemplate();
            } else {
                return $this->_template;
            }
        }
    }
    
    public function getLoadedCollection()
    {
        return $this->_getCollection();
    }
    
}