<?php
namespace MageArray\Wholesale\Model\Config\Source;

class WholesaleType implements \Magento\Framework\Option\ArrayInterface
{
    const NONE = 'none';
    const STOREW = 'w-store';
    const WEBW = 'w-webs';

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
            self::NONE => __('Select Store or Website'),
            self::STOREW => __('Wholesale Store'),
            self::WEBW => __('Wholesale Website')
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