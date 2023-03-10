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

class GoMage_ProductDesigner_Block_Adminhtml_Catalog_Product_Helper_Form_Gallery_Content
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    /**
     * Set Template
     */
    public function __construct()
    {
        parent::__construct();
        $_helper = Mage::helper('gomage_designer');
        $allowedProductTypes = $_helper->getAllowedProductTypes();
        if (in_array($this->_getProductType(), $allowedProductTypes) && $_helper->isEnabled()) {
            $this->setTemplate('gomage/productdesigner/catalog/product/helper/gallery.phtml');
        }
    }

    /**
     * Return Product designer enabled
     *
     * @return bool
     */
    public function isProductDesignerEnabled()
    {
        $product = $this->getProduct();

        if ($product && $product->getId()) {
            return Mage::getStoreConfig('gomage_designer/general/enabled', Mage::app()->getStore())
                && $product->getEnableProductDesigner();
        }

        return false;
    }

    /**
     * Return product design areas
     *
     * @return bool
     */
    public function getProductDesignAreas()
    {

        $product = $this->getProduct();
        $settings = array();
        if ($product && $product->getId()){
            foreach ($product->getMediaGallery('images') as $image) {
                if ($image['design_area']) {
                    $settings[$image['value_id']] = Mage::helper('core')->jsonDecode($image['design_area']);
                }
            }

            return Mage::helper('core')->jsonEncode($settings);
        }
        return array();
    }

    /**
     * Return update state url
     *
     * @return string
     */
    public function getUpdateStateUrl()
    {
        $product = $this->getProduct();

        if ($product && $product->getId()){
            return Mage::helper('adminhtml')->getUrl('*/designer_product/updateState', array(
                'product_id' => $product->getId()
            ));
        }

        return '';
    }

    /**
     * Return edit design area url
     *
     * @return bool|string
     */
    public function getEditDesignAreaUrl()
    {
        $product = $this->getProduct();

        if ($product && $product->getId()){
            return Mage::helper('adminhtml')->getUrl('*/designer_product/edit', array(
                'product_id' => $product->getId()
            ));
        }

        return false;
    }

    /**
     * Return product
     *
     * @return bool|Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = Mage::registry("product");
        if ($product && $product->getId()) {
            return $product;
        }

        return false;
    }

    public function  getProductColors()
    {
        $product = $this->getProduct();
        if ($product && $this->hasColorAttribute()) {
            return $product->getProductColors();
        }

        return false;
    }

    public function hasColorAttribute()
    {
        return Mage::helper('gomage_designer')->hasColorAttribute();
    }

    public function getDesignAreaPopupWidth()
    {
        return Mage::helper('gomage_designer')->getDesignWidth();
    }

    protected function _getProductType()
    {
        $product = $this->getProduct();

        if ($product && $product->getId())
        {
            return $product->getTypeId();
        }

        return $this->getRequest()->getParam('type', false);
    }
}
