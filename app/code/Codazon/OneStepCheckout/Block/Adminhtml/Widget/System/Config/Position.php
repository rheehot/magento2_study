<?php

namespace Codazon\OneStepCheckout\Block\Adminhtml\Widget\System\Config;

class Position extends \Codazon\OneStepCheckout\Block\Adminhtml\Widget\System\Config\ConfigAbstract
{
    protected $_template = 'Codazon_OneStepCheckout::system/config/position.phtml';

    public function isHasPrefixName() {
        $prefixName = $this->_scopeConfig->getValue('customer/address/prefix_options');
        if ($prefixName) {
            return true;
        } else {
            return false;
        }
    }

    public function isHasMiddleName() {
        $middleName = $this->_scopeConfig->getValue('customer/address/middlename_show');
        if ($middleName) {
            return true;
        } else {
            return false;
        }
    }

    public function isHasSuffixName() {
        $suffix = $this->_scopeConfig->getValue('customer/address/suffix_show');
        if ($suffix) {
            return true;
        } else {
            return false;
        }
    }

    public function isHasVatId() {
        $taxVat = $this->_scopeConfig->getValue('customer/create_account/vat_frontend_visibility');
        if ($taxVat) {
            return true;
        } else {
            return false;
        }
    }

    public function isHasGender() {
        $gender = $this->_scopeConfig->getValue('customer/address/gender_show');
        if ($gender) {
            return true;
        } else {
            return false;
        }
    }
   
    public function getFieldOptions()
    {
        $fieldOptions = array(
            '0'          => __('-- Please Select --'),
            'firstname'  => __('First Name'),
            'lastname'   => __('Last Name'),
            'company'    => __('Company'),
            'street'     => __('Address'),
            'country_id' => __('Country'),
            'region_id'     => __('State/Province'),
            'city'       => __('City'),
            'postcode'   => __('Zip/Postal Code'),
            'telephone'  => __('Telephone')
        );

        if ($this->isHasSuffixName()) {
            $fieldOptions['suffix'] =  __('Suffix Name');
        }

        if ($this->isHasMiddleName()) {
            $fieldOptions['middlename'] =  __('Middle Name');
        }

        if ($this->isHasPrefixName()) {
            $fieldOptions['prefix'] =  __('Prefix Name');
        }

        if ($this->isHasVatId()) {
            $fieldOptions['vat_id'] =  __('Tax/VAT number');
        }

        if ($this->isHasGender()) {
            $fieldOptions['gender'] =  __('Gender');
        }

        return $fieldOptions;
    }

    public function getDefaultField($number, $scope, $scopeId)
    {
        return $this->_scopeConfig
            ->getValue('opcheckout/field_position_management/row_' . $number, $scope, $scopeId);
    }

    public function getFieldEnableBackEnd($number, $scope, $scopeId)
    {
        $configCollection = $this->_dataConfigCollectionFactory->create()
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $scopeId)
            ->addFieldToFilter('path', 'opcheckout/field_position_management/row_' . $number);

        if (count($configCollection)) {
            return $configCollection->getFirstItem()->getData('value');
        } else {
            return null;
        }
    }

    public function getElementHtmlId($number)
    {
        return 'opcheckout_field_position_management_row_' . $number;
    }

    public function getElementHtmlName($number)
    {
        return 'groups[field_position_management][fields][row_' . $number . '][value]';
    }

    public function getCheckBoxElementHtmlId($number)
    {
        return 'opcheckout_field_position_management_row_' . $number . '_inherit';
    }

    public function getCheckBoxElementHtmlName($number)
    {
        return 'groups[field_position_management][fields][row_' . $number . '][inherit]';
    }
}