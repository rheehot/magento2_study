<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageArray\HidePrice\Framework\Pricing\Render;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;

/**
 * Price amount renderer
 *
 * @method string getAdjustmentCssClasses()
 * @method string getDisplayLabel()
 * @method string getPriceId()
 * @method bool getIncludeContainer()
 * @method bool getSkipAdjustments()
 */
class Amount extends \Magento\Framework\Pricing\Render\Amount
{
    /**
     * @param Template\Context $context
     * @param AmountInterface $amount
     * @param PriceCurrencyInterface $priceCurrency
     * @param RendererPool $rendererPool
     * @param SaleableInterface $saleableItem
     * @param \Magento\Framework\Pricing\Price\PriceInterface $price
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Pricing\Amount\AmountInterface $amount,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Render\RendererPool $rendererPool,
        \Magento\Framework\Pricing\SaleableInterface $saleableItem = null,
        \Magento\Framework\Pricing\Price\PriceInterface $price = null,
        \MageArray\HidePrice\Helper\Data $_helperData,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $amount, $priceCurrency, $rendererPool, $saleableItem, $price, $data);
        $this->amount = $amount;
        $this->saleableItem = $saleableItem;
        $this->price = $price;
        $this->priceCurrency = $priceCurrency;
        $this->rendererPool = $rendererPool;
        $this->_dataHelper = $_helperData;
        $this->customerSession = $customerSession;
    }

    /**
     * @param float $value
     * @return void
     */
    public function setDisplayValue($value)
    {
        $this->displayValue = $value;
    }

    /**
     * @return float
     */
    public function getDisplayValue()
    {
        if ($this->displayValue !== null) {
            return $this->displayValue;
        } else {
            return $this->getAmount()->getValue();
        }
    }

    /**
     * @return AmountInterface
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return SaleableInterface
     */
    public function getSaleableItem()
    {
        return $this->saleableItem;
    }

    /**
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getDisplayCurrencyCode()
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }

    /**
     * @return string
     */
    public function getDisplayCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * @return bool
     */
    public function hasAdjustmentsHtml()
    {
        return (bool)count($this->adjustmentsHtml);
    }

    /**
     * @return string
     */
    public function getAdjustmentsHtml()
    {
        return implode('', $this->adjustmentsHtml);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $active = $this->_dataHelper->getIsActive();
        $hideByCustomerGroup = $this->_dataHelper->getHideByCustomerGroup();

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

        $hideProductByCustGroup = $this->saleableItem
            ->getHidepriceByCustomergroup();

        if (!empty($hideProductByCustGroup)) {
            $hideProductByCustGroupArray = explode(",", $hideProductByCustGroup);
        }

        if ($active) {
            if ($isLoggedIn) {
                if ($hideByCustomerGroup == 1) {
                    if (!empty($hideProductByCustGroup)) {
                        if (
                        in_array($currentCustomerGroupId, $hideProductByCustGroupArray
                        )
                        ) {
                            $adjustmentRenders = $this->getAdjustmentRenders();
                            if ($adjustmentRenders) {
                                $adjustmentHtml = $this->getAdjustments($adjustmentRenders);
                                if (!$this->hasSkipAdjustments() ||
                                    ($this->hasSkipAdjustments()
                                        && $this->getSkipAdjustments() == false
                                    )
                                ) {
                                    $this->adjustmentsHtml = $adjustmentHtml;
                                }
                            }
                            $html = parent::_toHtml();
                            return $html;
                        } else {
                            $html = '<a href="' .
                                $this->_storeManager->getStore()->getBaseUrl() .
                                $this->_dataHelper->getLinkUrl() . '">' .
                                $this->_dataHelper->getDisplayText() . '</a>';
                            return $html;
                        }
                    } else {
                        if (
                        in_array($currentCustomerGroupId, $disableCustIdArray)
                        ) {
                            $html = '<a href="' .
                                $this->_storeManager->getStore()->getBaseUrl() .
                                $this->_dataHelper->getLinkUrl() . '">' .
                                $this->_dataHelper->getDisplayText() . '</a>';
                            return $html;
                        } else {
                            $adjustmentRenders = $this->getAdjustmentRenders();
                            if ($adjustmentRenders) {
                                $adjustmentHtml = $this->getAdjustments($adjustmentRenders);
                                if (!$this->hasSkipAdjustments() ||
                                    ($this->hasSkipAdjustments()
                                        && $this->getSkipAdjustments() == false
                                    )
                                ) {
                                    $this->adjustmentsHtml = $adjustmentHtml;
                                }
                            }
                            $html = parent::_toHtml();
                            return $html;
                        }
                    }
                } else {
                    $adjustmentRenders = $this->getAdjustmentRenders();
                    if ($adjustmentRenders) {
                        $adjustmentHtml = $this->getAdjustments(
                            $adjustmentRenders
                        );
                        if (!$this->hasSkipAdjustments() ||
                            ($this->hasSkipAdjustments()
                                && $this->getSkipAdjustments() == false)
                        ) {
                            $this->adjustmentsHtml = $adjustmentHtml;
                        }
                    }
                    $html = parent::_toHtml();
                    return $html;
                }
            } else {
                if ($this->saleableItem->getHidePrice() != 0) {
                    $html = '<a href="' .
                        $this->_storeManager->getStore()->getBaseUrl() .
                        $this->_dataHelper->getLinkUrl() . '">' .
                        $this->_dataHelper->getDisplayText() . '</a>';
                    return $html;
                } else {
                    $adjustmentRenders = $this->getAdjustmentRenders();
                    if ($adjustmentRenders) {
                        $adjustmentHtml = $this->getAdjustments($adjustmentRenders);
                        if (!$this->hasSkipAdjustments() ||
                            ($this->hasSkipAdjustments()
                                && $this->getSkipAdjustments() == false)
                        ) {
                            $this->adjustmentsHtml = $adjustmentHtml;
                        }
                    }
                    $html = parent::_toHtml();
                    return $html;
                }
            }
        } else {
            $adjustmentRenders = $this->getAdjustmentRenders();
            if ($adjustmentRenders) {
                $adjustmentHtml = $this->getAdjustments($adjustmentRenders);
                if (!$this->hasSkipAdjustments() ||
                    ($this->hasSkipAdjustments()
                        && $this->getSkipAdjustments() == false)
                ) {
                    $this->adjustmentsHtml = $adjustmentHtml;
                }
            }
            $html = parent::_toHtml();
            return $html;
        }
    }

    /**
     * @return AdjustmentRenderInterface[]
     */
    protected function getAdjustmentRenders()
    {
        return $this->rendererPool
            ->getAdjustmentRenders($this->saleableItem, $this->price);
    }

    /**
     * @param AdjustmentRenderInterface[] $adjustmentRenders
     * @return array
     */
    protected function getAdjustments($adjustmentRenders)
    {
        $this->setAdjustmentCssClasses($adjustmentRenders);
        $data = $this->getData();
        $adjustments = [];
        foreach ($adjustmentRenders as $adjustmentRender) {
            $adjustmentCode = $adjustmentRender->getAdjustmentCode();
            if ($this->getAmount()
                    ->getAdjustmentAmount($adjustmentCode) !== false
            ) {
                $html = $adjustmentRender->render($this, $data);
                if (trim($html)) {
                    $adjustments[$adjustmentRender->getAdjustmentCode()] = $html;
                }
            }
        }
        return $adjustments;
    }

    /**
     * Format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return float
     */
    public function formatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->priceCurrency
            ->format($amount, $includeContainer, $precision);
    }

    /**
     * @param AdjustmentRenderInterface[] $adjustmentRenders
     * @return array
     */
    protected function setAdjustmentCssClasses($adjustmentRenders)
    {
        $cssClasses =
            $this->hasData('css_classes')
                ? explode(' ', $this->getData('css_classes'))
                : [];
        $cssClasses = array_merge($cssClasses, array_keys($adjustmentRenders));
        $this->setData('adjustment_css_classes', join(' ', $cssClasses));
        return $this;
    }
}