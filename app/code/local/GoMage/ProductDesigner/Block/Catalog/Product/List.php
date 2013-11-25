<?php 
/**
 *  extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the GoMage ProductDesigner module to newer versions in the future.
 * If you wish to customize the GoMage ProductDesigner module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @copyright  Copyright (C) 2013 
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     
 */
class GoMage_ProductDesigner_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{
    public function getProductButtons()
    {
        $products = $this->getLoadedProductCollection();
        $_disabledAddToCart = Mage::getStoreConfig('gomage_designer/general/add_to_cart_button');
        $buttons = array();

        if (!Mage::helper('gomage_designer')->isEnabled()) {
            return $buttons;
        }
        foreach ($products as $_product) {
            $buttons[] = array(
                'add_to_cart_enabled' => (!$_product->getEnableProductDesigner() || !$_disabledAddToCart) ? true : false,
                'add_to_design_enabled' => $_product->getEnableProductDesigner() ? true : false,
                'design_url' => $this->_getDesignUrl($_product)
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
            return  '';
        }

        return $this->getUrl('designer', array('_query' => array('id' => $product->getId())));
    }
}
