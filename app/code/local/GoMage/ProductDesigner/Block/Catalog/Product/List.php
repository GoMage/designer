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
class GoMage_ProductDesigner_Block_Catalog_Product_List extends Mage_Core_Block_Template
{
    public function getProductButtons()
    {
        $products = Mage::app()->getLayout()->getBlock('product_list')->getLoadedProductCollection();
        $_disabledAddToCart = Mage::getStoreConfig('gomage_designer/general/add_to_cart_button');
        $buttons            = array();

        if (!Mage::helper('gomage_designer')->isEnabled()) {
            return $buttons;
        }
        foreach ($products as $_product) {
            $buttons[] = array(
                'add_to_cart_enabled'   => (!$_product->getEnableProductDesigner() || !$_disabledAddToCart) ? true : false,
                'add_to_design_enabled' => $_product->getEnableProductDesigner() ? true : false,
                'design_url'            => $this->_getDesignUrl($_product)
            );
        }

        return $buttons;
    }

    public function getProductButtonsJson()
    {
        return Zend_Json::encode($this->getProductButtons());
    }

    protected function _getDesignUrl($product)
    {
        if (!$product->getEnableProductDesigner()) {
            return '';
        }

        return $this->getUrl('designer', array('_query' => array('id' => $product->getId())));
    }
}
