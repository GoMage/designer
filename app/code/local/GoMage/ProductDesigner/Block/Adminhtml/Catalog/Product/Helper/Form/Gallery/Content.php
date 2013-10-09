<?php

class GoMage_ProductDesigner_Block_Adminhtml_Catalog_Product_Helper_Form_Gallery_Content
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    /**
     * Set Template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/catalog/product/helper/gallery.phtml');
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
            return Mage::getStoreConfig('gmpd/general/enabled', Mage::app()->getStore())
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
        if ($product && $product->getId() && $product->getEnableProductDesigner()){
            foreach ($product->getMediaGallery('images') as $image) {
                $settings[$image['value_id']] = Mage::helper('core')->jsonDecode($image['design_area']);
            }

            return Mage::helper('core')->jsonEncode($settings);
        }
        return array();
    }

    /**
     * Return update state url
     *
     * @return bool|string
     */
    public function getUpdateStateUrl()
    {
        $product = $this->getProduct();

        if ($product && $product->getId()){
            return Mage::helper('adminhtml')->getUrl('*/designer_product/updateState', array(
                'product_id' => $product->getId()
            ));
        }

        return false;
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
        if ($product && $this->hasColorAttribute() && $product->getEnableProductDesigner()) {
            return $product->getProductColors();
        }

        return false;
    }

    public function hasColorAttribute()
    {
        return Mage::helper('designer')->hasColorAttribute();
    }
}