<?php

namespace Codazon\OneStepCheckout\Block\System\Config;

class FieldPosition extends \Magento\Config\Block\System\Config\Form\Fieldset
{
	protected function _prepareLayout()
    {
        $this->addChild('position_block', 'Codazon\OneStepCheckout\Block\Adminhtml\Widget\System\Config\Position');

        return parent::_prepareLayout();
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getChildHtml('position_block');
    }
}