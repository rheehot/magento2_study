<?php


namespace Mastering\SampleModule\Model\ResourceModel\Item;

use Codazon\ProductLabel\Model\ResourceModel\Collection\AbstractCollection;
use Mastering\SampleModule\Model\Item;
use Mastering\SampleModule\Model\ResourceModel\Item as ItemResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(Item::class, ItemResource::class);
    }

}