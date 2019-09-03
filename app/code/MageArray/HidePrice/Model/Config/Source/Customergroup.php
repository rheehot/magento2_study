<?php
/**
 * Used in creating options for Customer Group config value selection
 *
 */
namespace MageArray\HidePrice\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Class CustomerGroup
 * @package MageArray\HidePrice\Model\Config\Source
 */
class CustomerGroup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * CustomerGroup constructor.
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
    public function toOptionArray()
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