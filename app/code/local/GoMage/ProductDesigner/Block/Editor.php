<?php
/**
 * GoMage.com
 *
 * GoMage ProductDesigner Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */
class GoMage_ProductDesigner_Block_Editor extends Mage_Core_Block_Template
{
    protected $_editorConfig;
    /**
     * Return Customer Id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getCustomerId();
    }

    /**
     * Return current product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = Mage::registry('current_product');
        return $product;
    }

    /**
     * Return editor config
     *
     * @return array
     */
    public function getEditorConfig()
    {
        if (is_null($this->_editorConfig)) {
            $this->_editorConfig = Mage::helper('designer')->getProductSettingForEditor();
        }

        return $this->_editorConfig;
    }

    public function getEditorConfigJson()
    {
        return Zend_Json::encode($this->getEditorConfig());
    }

    public function isNavigationEnabled()
    {
        return Mage::getStoreConfig('gmpd/navigation/enabled', Mage::app()->getStore());
    }

    public function isDesignEnabled()
    {
        return Mage::getStoreConfig('gmpd/design/enabled', Mage::app()->getStore());
    }

    public function isTextEnabled()
    {
        return Mage::getStoreConfig('gmpd/text/enabled', Mage::app()->getStore());
    }

    public function isUploadImageEnabled()
    {
        return Mage::getStoreConfig('gmpd/upload_image/enabled', Mage::app()->getStore());
    }

    public function isProductSelected()
    {
        return (bool) $this->getProduct()->getId();
    }

    public function getProductImageWidth()
    {
        return $imageWidth = Mage::getStoreConfig('gmpd/design/design_size_width');
    }

    public function isCustomerLoggedIn()
    {
        return (bool) $this->getCustomerId();
    }
}