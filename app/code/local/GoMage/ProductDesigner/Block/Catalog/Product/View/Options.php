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
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Block_Catalog_Product_View_Options extends Mage_Catalog_Block_Product_View_Options
{
    protected $_designOption;

    /**
     * Return product design option
     *
     * @return bool|Mage_Catalog_Model_Product_Option
     */
    public function getDesignOption()
    {
        if (is_null($this->_designOption)) {

            $helper = Mage::helper('gomage_designer');

            $design = $helper->getProductDesign($this->getProduct());
            if ($design && $design->getId()) {

                $option = Mage::getModel('catalog/product_option')
                    ->setData(array(
                            'value' => $design->getId(),
                            'type'  => Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX,
                            'title' => $helper->__('Design'),
                        )
                    )
                    ->setProduct($this->getProduct())
                    ->setId(GoMage_ProductDesigner_Model_Design::CUSTOM_OPTION_ID);

                $option_value = Mage::getModel('catalog/product_option_value')
                    ->setData(array(
                            'title'      => $helper->__('Custom Design'),
                            'price_type' => 'fixed',
                            'price'      => $design->getPrice(),
                        )
                    )
                    ->setProduct($this->getProduct())
                    ->setId($design->getId());

                $option->addValue($option_value);

                $this->_designOption = $option;
            } else {
                $this->_designOption = false;
            }
        }

        return $this->_designOption;
    }


    public function getOptions()
    {
        $options = parent::getOptions();

        if ($design_option = $this->getDesignOption()) {
            $options[] = $design_option;
        }
        return $options;
    }

}
