<?php
namespace MageArray\Wholesale\Model\Config\Source;

class VatTax implements \Magento\Framework\Option\ArrayInterface
{
    const NONE = '';
    const OPTIONAL = 'opt';
    const REQUIRED = 'req';

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
            self::NONE => __('No'),
            self::OPTIONAL => __('Optional'),
            self::REQUIRED => __('Required')
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