<?php
namespace MageArray\Wholesale\Model\Config\Source;

class WholesaleSelectType implements \Magento\Framework\Option\ArrayInterface
{
    const NONE = 0;
    const EXISTING = 1;
    const CREATE = 2;

    public function toOptionArray()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    public static function getOptionArray()
    {
        return [
            self::NONE => __('Select Option'),
            self::EXISTING => __('Select Existing Website'),
            self::CREATE => __('Create New Website')
        ];
    }

    public function getAllOptions()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}