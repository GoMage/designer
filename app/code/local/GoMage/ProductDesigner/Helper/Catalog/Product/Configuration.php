<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.2.0
 * @since        Available since Release 2.0.0
 */
class GoMage_ProductDesigner_Helper_Catalog_Product_Configuration extends Mage_Catalog_Helper_Product_Configuration
{

    public function getCustomOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $options = parent::getCustomOptions($item);

        $design_option = $item->getOptionByCode('design');
        if ($design_option && $design_option->getValue()) {
            $helper    = Mage::helper('gomage_designer');
            $options[] = array(
                'label'       => $helper->__('Design'),
                'value'       => $helper->__('Custom Design'),
                'print_value' => $helper->__('Custom Design'),
                'option_id'   => GoMage_ProductDesigner_Model_Design::CUSTOM_OPTION_ID,
                'option_type' => 'checkbox',
                'custom_view' => false
            );
        }

        return $options;
    }

}
