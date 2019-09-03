<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageArray\HidePrice\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\PriceBox as PriceBoxRender;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\View\Element\Template\Context;

/**
 * Default catalog price box render
 *
 * @method string getPriceElementIdPrefix()
 * @method string getIdSuffix()
 */
class PriceBox extends PriceBoxRender
{

    /**
     * @var \MageArray\HidePrice\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param Context $context
     * @param Product $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        Product $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        \MageArray\HidePrice\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_product = $saleableItem;
        $this->_dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $saleableItem, $price, $rendererPool);
    }

    /**
     * @return mixed
     */
    protected function _toHtml()
    {
        $active = $this->_dataHelper->getIsActive();
        $hideByCustomerGroup = $this->_dataHelper->getHideByCustomerGroup();
        $hideProductPrice = $this->_product->getHidePrice();
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

        if ($hideByCustomerGroup != 0) {
            $disableCustomerId = $this->_dataHelper->getCustomerGroupId();
            $disableCustIdArray = explode(",", $disableCustomerId);
        }

        $hideProductByCustGroup = $this->_product
            ->getHidepriceByCustomergroup();

        if (!empty($hideProductByCustGroup)) {
            $hideProductByCustGroupArray = explode(",", $hideProductByCustGroup);
        }

        if ($active) {
            if ($isLoggedIn) {
                if ($hideByCustomerGroup == 1) {
                    if (!empty($hideProductByCustGroup)) {
                        if (in_array($currentCustomerGroupId, $hideProductByCustGroupArray)) {
                            $cssClasses = $this->hasData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
                            $cssClasses[] = 'price-' .
                                $this->getPrice()->getPriceCode();
                            $this->setData('css_classes', implode(' ', $cssClasses));
                            return parent::_toHtml();

                        } else {

                        }
                    } else {
                        if (in_array($currentCustomerGroupId, $disableCustIdArray)) {

                        } else {
                            $cssClasses = $this->hasData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
                            $cssClasses[] = 'price-' . $this->getPrice()->getPriceCode();
                            $this->setData('css_classes', implode(' ', $cssClasses));
                            return parent::_toHtml();
                        }
                    }
                } else {
                    $cssClasses = $this->hasData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
                    $cssClasses[] = 'price-' . $this->getPrice()->getPriceCode();
                    $this->setData('css_classes', implode(' ', $cssClasses));
                    return parent::_toHtml();
                }
            } else {
                if ($hideProductPrice != 0) {

                } else {
                    $cssClasses = $this->hasData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
                    $cssClasses[] = 'price-' . $this->getPrice()->getPriceCode();
                    $this->setData('css_classes', implode(' ', $cssClasses));
                    return parent::_toHtml();
                }
            }
        } else {
            $cssClasses = $this->hasData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
            $cssClasses[] = 'price-' . $this->getPrice()->getPriceCode();
            $this->setData('css_classes', implode(' ', $cssClasses));
            return parent::_toHtml();
        }
    }
}