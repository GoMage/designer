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
class GoMage_ProductDesigner_Block_Adminhtml_Product_Edit extends Mage_Core_Block_Template
{
    protected $_image;

    /**
     * Return product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Return image settings
     *
     * @return array
     */
    public function getSettings()
    {
        $product = $this->getProduct();
        $imageId = $this->getImageId();

        if (!$product->getId()) {
            return array();
        }
        $settings = $product->getDesignAreas();

        if ($settings == null) {
            $settings = array();
        } else {
            $settings = Mage::helper('designer')->jsonDecode($settings);
        }

        if (isset($settings[$imageId])) {
            $settings = $settings[$imageId];
        } else {
            $settings = array(
                't' => round($imageHeight / 2),
                'l' => round($imageWidth / 2),
                'h' => 100,
                'w' => 100,
                's' => 1,
                'ip' => 0,
            );
        }

        return $settings;
    }

    /**
     * Return image
     *
     * @return Varien_Object
     */
    public function getImage()
    {
        if (is_null($this->_image)) {
            $product = $this->getProduct();
            if ($product->getId()) {
                $image = Mage::registry('current_image');
                if ($image && $image->getId()) {
                    $imageUrl = Mage::helper('designer')->getDesignImageUrl($product, $image);
                    $image->setUrl($imageUrl);
                    list($imageWidth, $imageHeight) = Mage::helper('designer')->getImageDimensions($image->getUrl());
                    $image->setWidth($imageWidth);
                    $image->setHeight($imageHeight);
                    $this->_image = $image;
                }
            }
        }

        return $this->_image;
    }

    /**
     * Return image Id
     *
     * @return int
     */
    public function getImageId()
    {
        return $this->getRequest()->getParam('img');
    }

    /**
     * Return Image width
     *
     * @return int
     */
    public function getImageWidth()
    {
        return $this->getImage()->getWidth();
    }

    /**
     * Return Image height
     *
     * @return int
     */
    public function getImageHeight()
    {
        return $this->getImage()->getHeight();
    }

    /**
     * Return save url
     *
     * @return bool|string
     */
    public function getSaveUrl()
    {
        $product = $this->getProduct();

        if ($product && $product->getId()){
            return Mage::helper('adminhtml')->getUrl('*/designer_product/save', array(
                'product_id' => $product->getId()
            ));
        }

        return false;
    }
}