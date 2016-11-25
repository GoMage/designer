<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.4.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Block_Adminhtml_Sales_Items_Column_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    public function getOrderDesignOption()
    {
        $options   = $this->getItem()->getProductOptionByCode('options');
        $design_id = 0;
        foreach ($options as $option) {
            if (isset($option['option_id']) && $option['option_id'] == GoMage_ProductDesigner_Model_Design::CUSTOM_OPTION_ID) {
                $design_id = (int)$option['option_value'];
                break;
            }
        }
        if ($design_id) {
            $design = Mage::getModel('gomage_designer/design')->load($design_id);
            if ($design && $design->getId()) {
                $option = array(
                    'price'     => $design->getPrice(),
                    'design_id' => $design->getId(),
                    'url'       => Mage::helper('adminhtml')->getUrl('*/designer_design/view',
                        array('design_id' => $design->getId())
                    )
                );

                return new Varien_Object($option);
            }
        }

        return false;
    }

    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption($value)
    {
        $_remainder = '';
        $value      = Mage::helper('core/string')->truncate($value, 55, '', $_remainder);
        $result     = array(
            'value'     => nl2br($value),
            'remainder' => nl2br($_remainder)
        );

        return $result;
    }
}
