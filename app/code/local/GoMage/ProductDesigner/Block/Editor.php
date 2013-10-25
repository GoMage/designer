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
        $product = Mage::registry('product');
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
            if ($this->isProductSelected()) {
                $this->_editorConfig = Mage::helper('designer')->getProductSettingForEditor();
            } else {
                $this->_editorConfig = false;
            }

        }

        return $this->_editorConfig;
    }

    public function getProductImages()
    {
        $config = $this->getEditorConfig();
        $defaultColor = $config['default_color'];
        if (isset($config['images'][$defaultColor])) {
            return $config['images'][$defaultColor];
        }

        return array();
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
        $product = $this->getProduct();
        return (bool) $product->getId() && ($product->getEnableProductDesigner()
            && $product->hasImagesForDesign());
    }

    public function getProductImageWidth()
    {
        return $imageWidth = Mage::getStoreConfig('gmpd/design/design_size_width');
    }

    public function getProductOriginalImageMinSizes()
    {
        return array(
            'width' => Mage::getStoreConfig('gmpd/design/zoom_size_width'),
            'height' => Mage::getStoreConfig('gmpd/design/zoom_size_height')
        );
    }

    public function getProductOriginalImageMinSizesJson()
    {
        return Zend_Json::encode($this->getProductOriginalImageMinSizes());
    }

    public function isCustomerLoggedIn()
    {
        return (bool) $this->getCustomerId();
    }

    /**
     * Return design price config
     *
     * @return array
     */
    public function getDesignPriceConfig()
    {
        $config = array(
            'fixed_price' => Mage::getStoreConfig('gmpd/general/fixed_price') ?:0,
            'text_price'  => Mage::getStoreConfig('gmpd/general/price_for_text')?:0,
            'image_text'  => Mage::getStoreConfig('gmpd/general/price_for_image')?:0
        );

        return $config;
    }

    /**
     * Return design price config json
     *
     * @return string
     */
    public function getDesignPriceConfigJson()
    {
        return Zend_Json::encode($this->getDesignPriceConfig());
    }

    public function  getProductColors()
    {
        if ($this->isProductSelected()) {
            return $this->getProduct()->getProductColors();
        }

        return false;
    }

    public function isHelpEnabled($area)
    {
        return (bool) Mage::getStoreConfig('gmpd/'. $area .'/show_help') &&
            $this->getHelpText($area);
    }

    public function getHelpPopupWidth($area)
    {
        return Mage::getStoreConfig('gmpd/'. $area .'/popup_width');
    }

    public function getHelpPopupHeight($area)
    {
        return Mage::getStoreConfig('gmpd/'. $area .'/popup_height');
    }

    public function getHelpText($area)
    {
        return Mage::getStoreConfig('gmpd/'. $area .'/popup_text');
    }

    public function additionalInstructionsEnabled()
    {
        return (bool) Mage::getStoreConfig('gmpd/general/show_comment');
    }
}