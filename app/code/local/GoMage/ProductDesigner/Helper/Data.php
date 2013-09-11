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
class GoMage_ProductDesigner_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $allowedProductTypes = array('simple', 'configurable');
    protected $_productSettings;


    public function isEnabled()
    {
        return Mage::getStoreConfig('gmpd/general/enabled', Mage::app()->getStore());
    }

    public function isCustomizable($product)
    {

    }

    public function isRemoveAddToCart($product)
    {

    }

    public function getProductDesingAreas()
    {

    }

    /**
     * Return Image Settings
     * 
     * @param Mage_Catalog_Model_Product $product Product
     * @param int                        $imageId Image Id
     * @return boolean|array
     */
    public function getImageSettings(Mage_Catalog_Model_Product $product, $imageId)
    {
        $settings = $this->getSettings($product);
        if (isset($settings[$imageId])) {
            return $settings[$imageId];
        }
        return false;
    }

    /**
     * Return Product images settings
     * 
     * @param Mage_Catalog_Model_Product $product Product
     * @return array
     */
    public function getSettings(Mage_Catalog_Model_Product $product) 
    {
        if (!$this->_productSettings) {
            $settings = $product->getDesignAreas();

            if ($settings == null) {
                $settings = array();
            } else {
                $settings = $this->jsonDecode($settings);
            }

            $images = $product->getMediaGalleryImages(true);
            foreach ($images as $image) {
                $imageId = $image->getValueId();
                if (isset($settings[$imageId])) {
                    $imageUrl = $this->getDesignImageUrl($product, $image);
                    $dimensions = $this->getImageDimensions($imageUrl);

                    $baseUrl = Mage::getBaseUrl('media');
                    $baseDir = Mage::getBaseDir('media') . DS;

                    $settings[$imageId]['path'] = str_replace($baseUrl, $baseDir, $imageUrl);
                    $settings[$imageId]['dimensions'] = array(
                        'width' => $dimensions[0],
                        'height' => $dimensions[1]
                    );
                }
            }
            $this->_productSettings = $settings;
        }
        return $this->_productSettings;
    }

    public function jsonDecode($string)
    {
        $settings = Mage::helper('core')->jsonDecode($string);
        $tmp = array();
        foreach ($settings as $i => $v) {
            $tmp[$i] = (array)$v;
            if (!isset($tmp[$i]['s'])) {
                $tmp[$i]['s'] = 1;
            }
        }
        $settings = $tmp;
        return $settings;
    }

    public function getImageDimensions($imagePath)
    {
        $dirImg = Mage::getBaseDir().str_replace("/", DS, strstr($imagePath, '/media'));

        if (file_exists($dirImg)) {
            $imageObj = new Varien_Image($dirImg);
            $width = $imageObj->getOriginalWidth();
            $height = $imageObj->getOriginalHeight();
            return array($width, $height);
        }

        return array(0, 0);
    }

    public function prepareDesignerSessionId() 
    {
        $customerSession = $this->_getCustomerSession();
        if(!$customerSession->getDesignerSessionId()) {
            $customerSession->setDesignerSessionId(sha1(rand(0,1000).microtime(true)));
        }
    }

    public function getDesignerSessionId() 
    {
        $this->prepareDesignerSessionId();
        $sessionId = $this->_getCustomerSession()->getDesignerSessionId();
        return $sessionId;
    }

    protected function _getCustomerSession() 
    {
        return Mage::getSingleton('customer/session');
    }
    
    /**
     * Return Image Url
     * 
     * @param Mage_Catalog_Model_Product $product Product
     * @param Varien_Object              $image   Image
     * @return string
     */
    public function getDesignImageUrl(Mage_Catalog_Model_Product $product, $image)
    {
        $imageWidth = Mage::getStoreConfig('gmpd/design/design_size_width');
        $imageHeight = Mage::getStoreConfig('gmpd/design/design_size_height');
        $url = Mage::helper('catalog/image')->init($product, 'base_image', $image->getFile())
            ->resize($imageWidth, $imageHeight)->__toString();
        
        return $url;
    }

    public function getProductSettingForEditor(Mage_Catalog_Model_Product $product = null)
    {
        if (is_null($product)) {
            $product = Mage::registry('current_product');
        }

        $editorConfig = array(
            'images' => array()
        );

        if (!$product->getId()) {
            return $editorConfig;
        }

        $images = $product->getMediaGalleryImages($fromDesignerPage = true);
        $settings = $product->getDesignAreas();

        if ($settings == null) {
            $settings = array();
        } else {
            $settings = Mage::helper('designer')->jsonDecode($settings);
        }

        foreach ($images as $image) {
            $id = $image->getValueId();
            if (!isset($settings[$id]) || isset($editorConfig['images'][$id])) {
                continue;
            }

            if (!isset($settings[$id]['on']) || $settings[$id]['on'] != 1) {
                continue;
            }

            unset($settings[$id]['on']);

            $imageUrl = Mage::helper('designer')->getDesignImageUrl($product, $image);
            $conf = $settings[$id];
            $conf['id'] = $id;
            $conf['u'] = $imageUrl;
            $conf['d'] = Mage::helper('designer')->getImageDimensions($imageUrl);
            $editorConfig['images'][] = $conf;
        }
        return $editorConfig;
    }
}
