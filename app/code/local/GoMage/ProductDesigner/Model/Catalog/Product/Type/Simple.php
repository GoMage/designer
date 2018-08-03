<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.6.0
 * @since        Available since Release 2.0.0
 */
class GoMage_ProductDesigner_Model_Catalog_Product_Type_Simple extends Mage_Catalog_Model_Product_Type_Simple
{

    /**
     * Prepare additional options/information for order item which will be
     * created from this product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getOrderOptions($product = null)
    {
        $optionArr = parent::getOrderOptions($product);

        if ($design_option = $this->getProduct($product)->getCustomOption('design')) {
            $helper                 = Mage::helper('gomage_designer');
            $optionArr['options'][] = array(
                'label'        => $helper->__('Design'),
                'value'        => $helper->__('Custom Design'),
                'print_value'  => $helper->__('Custom Design'),
                'option_id'    => GoMage_ProductDesigner_Model_Design::CUSTOM_OPTION_ID,
                'option_type'  => 'checkbox',
                'option_value' => $design_option->getValue(),
                'custom_view'  => false,
            );
        }
        return $optionArr;
    }

}
