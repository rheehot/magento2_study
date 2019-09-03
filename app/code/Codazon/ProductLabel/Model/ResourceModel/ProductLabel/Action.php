<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ProductLabel\Model\ResourceModel\ProductLabel;

/**
 * Catalog Product Mass processing resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Action extends \Codazon\ProductLabel\Model\ResourceModel\AbstractResource
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this->setType(
            'codazon_product_label_entity'
        )->setConnection(
            $resource->getConnection('productlabel')
        );
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     * @return $this
     * @throws \Exception
     */
    public function updateAttributes($entityIds, $attrData, $storeId)
    {
        $object = new \Magento\Framework\DataObject();
        $object->setStoreId($storeId);

        $this->getConnection()->beginTransaction();
        try {
            foreach ($attrData as $attrCode => $value) {
                $attribute = $this->getAttribute($attrCode);
                if(!is_object($attribute)){
					continue;
				}
				if (!$attribute->getAttributeId()) {
                    continue;
                }

                $i = 0;
                foreach ($entityIds as $entityId) {
                    $i++;
                    $object->setId($entityId);
                    $object->setEntityId($entityId);
                    // collect data for save
                    $this->_saveAttributeValue($object, $attribute, $value);
                    // save collected data every 1000 rows
                    if ($i % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                }
                $this->_processAttributeValues();
            }
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $this;
    }
}
