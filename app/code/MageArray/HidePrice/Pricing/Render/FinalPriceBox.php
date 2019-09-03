<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageArray\HidePrice\Pricing\Render;

use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Catalog\Pricing\Render\FinalPriceBox as FPB;

/**
 * Class for final_price rendering
 *
 * @method bool getUseLinkForAsLowAs()
 * @method bool getDisplayMinimalPrice()
 */
class FinalPriceBox extends FPB
{
    /**
     * @var \MageArray\HidePrice\Helper\Data
     */
    protected $_dataHelper;

    /**
     * FinalPriceBox constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param Render\RendererPool $rendererPool
     * @param \MageArray\HidePrice\Helper\Data $dataHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        \Magento\Framework\Pricing\Render\RendererPool $rendererPool,
        \MageArray\HidePrice\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context, $saleableItem, $price, $rendererPool, $data
        );
    }

    /**
     * Wrap with standard required container
     *
     * @param string $html
     * @return string
     */
    protected function wrapResult($html)
    {
        $active = $this->_dataHelper->getIsActive();
        $hidePrice = $this->getSaleableItem()->getHidePrice();
        /** @var \Magento\Framework\App\ObjectManager $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\Http\Context $context */
        $context = $om->get('Magento\Framework\App\Http\Context');
        /** @var bool $isLoggedIn */
        $isLoggedIn = $context->getValue(
            \Magento\Customer\Model\Context::CONTEXT_AUTH
        );
        $currentCustomerGroupId = $this->customerSession
            ->getcustomer_group_id();
        $hideByCustomerGroup = $this->_dataHelper->getHideByCustomerGroup();

        if ($hideByCustomerGroup != 0) {
            $disableCustomerId = $this->_dataHelper->getCustomerGroupId();
            $disableCustIdArray = explode(",", $disableCustomerId);
        }

        $hideProductByCustGroup = $this->getSaleableItem()
            ->getHidepriceByCustomergroup();

        if (!empty($hideProductByCustGroup)) {
            $hideProductByCustGroupArray = explode(",", $hideProductByCustGroup);
        }

        if ($active) {
            if ($isLoggedIn) {
                if ($hideByCustomerGroup == 1) {
                    if (!empty($hideProductByCustGroup)) {
                        if (in_array($currentCustomerGroupId, $hideProductByCustGroupArray)) {
                            return '<div class="price-box ' .
                            $this->getData('css_classes') . '" ' .
                            'data-role="priceBox" ' . 'data-product-id="' .
                            $this->getSaleableItem()->getId() . '"' .
                            '>' . $html . '</div>';
                        } else {
                            return '<div class="price-box ' .
                            $this->getData('css_classes') . '" ' .
                            'data-role="priceBox" ' .
                            'data-product-id="' .
                            $this->getSaleableItem()->getId() . '"' .
                            '><a href="' .
                            $this->_storeManager->getStore()->getBaseUrl() .
                            $this->_dataHelper->getLinkUrl() . '">' .
                            $this->_dataHelper->getDisplayText() .
                            '</a></div>';
                        }
                    } else {
                        if (in_array($currentCustomerGroupId, $disableCustIdArray)) {
                            return '<div class="price-box ' .
                            $this->getData('css_classes') . '" ' .
                            'data-role="priceBox" ' .
                            'data-product-id="' .
                            $this->getSaleableItem()->getId() . '"' .
                            '><a href="' . $this->_storeManager->getStore()->getBaseUrl() .
                            $this->_dataHelper->getLinkUrl() . '">' .
                            $this->_dataHelper->getDisplayText() .
                            '</a></div>';
                        } else {
                            return '<div class="price-box ' .
                            $this->getData('css_classes') . '" ' .
                            'data-role="priceBox" ' .
                            'data-product-id="' .
                            $this->getSaleableItem()->getId() . '"' .
                            '>' . $html . '</div>';
                        }
                    }
                } else {
                    return '<div class="price-box ' .
                    $this->getData('css_classes') . '" ' .
                    'data-role="priceBox" ' .
                    'data-product-id="' .
                    $this->getSaleableItem()->getId() . '"' .
                    '>' . $html . '</div>';
                }
            } else {
                if ($hidePrice != 0) {
                    return '<div class="price-box ' .
                    $this->getData('css_classes') . '" ' .
                    'data-role="priceBox" ' .
                    'data-product-id="' . $this->getSaleableItem()->getId() .
                    '"' . '><a href="' .
                    $this->_storeManager->getStore()->getBaseUrl() .
                    $this->_dataHelper->getLinkUrl() . '">' .
                    $this->_dataHelper->getDisplayText() .
                    '</a></div>';
                } else {
                    return '<div class="price-box ' .
                    $this->getData('css_classes') . '" ' .
                    'data-role="priceBox" ' .
                    'data-product-id="' .
                    $this->getSaleableItem()->getId() . '"' .
                    '>' . $html . '</div>';
                }
            }
        } else {
            return '<div class="price-box ' .
            $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() .
            '"' . '>' . $html . '</div>';
        }
    }
}