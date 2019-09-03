<?php

namespace MageArray\HidePrice\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Class Productcustomergroup
 * @package MageArray\HidePrice\Model\Config\Source
 */
class Productcustomergroup extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Productcustomergroup constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $options = $this->collectionFactory
            ->create()->setRealGroupsFilter()->toOptionArray();

        foreach ($options as $optionCode) {

            $customerGroupOptions[] = [
                'value' => $optionCode['value'],
                'label' => __($optionCode['label'])
            ];
        }

        return $customerGroupOptions;
    }
}