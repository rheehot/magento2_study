<?php
namespace MageArray\Wholesale\Model\Config\Source;

class Hide implements \Magento\Framework\Option\ArrayInterface
{
    const NONE = '';
    const PRICE = 1;
    const CATALOG = 2;

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
            self::PRICE => __('Hide Price'),
            self::CATALOG => __('Hide Catalog')
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