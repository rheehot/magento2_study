<?php

namespace Codazon\OneStepCheckout\Plugin\Checkout\Block\Checkout;

class LayoutProcessor
{

	public function afterProcess($subject, $jsLayout)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($objectManager->get('Codazon\OneStepCheckout\Helper\Config')->isEnabledOneStep()) {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children'])) {
                if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']))
                {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['component'] = 'Codazon_OneStepCheckout/js/view/billing-address';
                }
                foreach($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => $value){
                    if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['component']) && $value['component'] == 'Magento_Checkout/js/view/billing-address')
                    {
                        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['component'] = 'Codazon_OneStepCheckout/js/view/billing-address';
                    }
                }
                //print_r($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']);die;
            }
        }
        return $jsLayout;
    }
}