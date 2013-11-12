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
    protected $_allowedProductTypes = array(
        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
    );

    protected $_productSettings;

    protected $_productDesign = null;

    protected $_editorConfig = null;

    public function isEnabled()
    {
        return Mage::getStoreConfig('gomage_designer/general/enabled', Mage::app()->getStore());
    }

    public function isNavigationEnabled()
    {
        return Mage::getStoreConfig('gomage_designer/navigation/enabled', Mage::app()->getStore());
    }

    /**
     * Return allowed product types
     *
     * @return array
     */
    public function getAllowedProductTypes()
    {
        return $this->_allowedProductTypes;
    }

    public function hasColorAttribute()
    {
        $attributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
        return $attribute->getId();
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
            $settings = array();
            $images = $product->getMediaGallery('images');
            foreach ($images as $image) {
                $designArea = Mage::helper('core')->jsonDecode($image['design_area']);
                $imageId = $image['value_id'];
                if ($designArea && !empty($designArea)) {
                    $imageUrl = $this->getDesignImageUrl($product, $image);
                    $dimensions = $this->getImageDimensions($imageUrl);

                    $baseUrl = Mage::getBaseUrl('media');
                    $baseDir = Mage::getBaseDir('media') . DS;

                    $designArea['path'] = str_replace($baseUrl, $baseDir, $imageUrl);
                    $designArea['dimensions'] = array(
                        'width' => $dimensions[0],
                        'height' => $dimensions[1]
                    );
                    $designArea['original_image'] = $this->getOriginalImage($product, $image);
                    if (isset($designArea['original_image']['url'])) {
                        $designArea['original_image']['path'] = str_replace(
                            $baseUrl, $baseDir, $designArea['original_image']['url']
                        );
                    }
                    $settings[$imageId] = $designArea;
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
        $sessionId = $this->_getCustomerSession()->getEncryptedSessionId();
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
    public function getDesignImageUrl(Mage_Catalog_Model_Product $product, $image, $size = array())
    {
        if (empty($size)) {
            $imageWidth = Mage::getStoreConfig('gomage_designer/design/design_size_width');
            $imageHeight = Mage::getStoreConfig('gomage_designer/design/design_size_height');
        } else {
            list($imageWidth, $imageHeight) = $size;
        }

        $imageFile = is_object($image) ? $image->getFile() : $image['file'];
        $url = Mage::helper('catalog/image')->init($product, 'base_image', $imageFile)
            ->resize($imageWidth, $imageHeight)->__toString();

        return $url;
    }

    public function getOriginalImage(Mage_Catalog_Model_Product $product, $image)
    {
        $imageFile = is_object($image) ? $image->getFile() : $image['file'];
        $imagePath = $product->getMediaConfig()->getMediaPath($imageFile);
        $minWidth = Mage::getStoreConfig('gomage_designer/design/zoom_size_width');
        $minHeight = Mage::getStoreConfig('gomage_designer/design/zoom_size_height');
        if (file_exists($imagePath)) {
            $imageObj = new Varien_Image($imagePath);
            $width = $imageObj->getOriginalWidth();
            $height = $imageObj->getOriginalHeight();
            $dimensions = array();
            if ($width < $minWidth || $height < $minHeight) {
                $dimensions[0] = $minWidth;
                $dimensions[1] = $minHeight;
            } else {
                $dimensions[0] = $width;
                $dimensions[1] = $height;
            }
            return array(
                'url' => $this->getDesignImageUrl($product, $image, $dimensions),
                'dimensions' => $dimensions
            );
        }

        return array();
    }

    public function getProductSettingForEditor(Mage_Catalog_Model_Product $product = null)
    {
        if (!$this->_editorConfig) {
            if (is_null($product)) {
                $product = Mage::registry('product');
            }

            $editorConfig = array(
                'images' => array()
            );

            if (!$product->getId()) {
                return $editorConfig;
            }

            $images = $product->getMediaGallery('images');
            $colorAttributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
            $defaultColor = null;
            foreach ($images as $image) {
                $id = $image['value_id'];
                $settings = Mage::helper('core')->jsonDecode($image['design_area']);
                if (!$settings || empty($settings)) {
                    continue;
                }
                $imageUrl = $this->getDesignImageUrl($product, $image);
                $conf = $settings;
                $conf['id'] = $id;
                $conf['u'] = $imageUrl;
                $conf['d'] = $this->getImageDimensions($imageUrl);
                $conf['orig_image'] = $this->getOriginalImage($product, $image);

                if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    if ($image['color']) {
                        if (is_null($defaultColor)) {
                            $defaultColor = $image['color'];
                        }
                        if (!isset($editorConfig['images'][$image['color']])) {
                            $editorConfig['images'][$image['color']] = array();
                        }
                        $editorConfig['images'][$image['color']][$id] = $conf;
                    } else {
                        $defaultColor = $product->getData($colorAttributeCode) ?:'none_color';
                        $editorConfig['images'][$defaultColor][$id] = $conf;
                    }
                } else {
                    $defaultColor = 'none_color';
                    $editorConfig['images'][$defaultColor][$id] = $conf;
                }
                $editorConfig['default_color'] = $defaultColor;
                $editorConfig['url'] = $product->getProductUrl();
            }

            $this->_editorConfig = $editorConfig;
        }

        return $this->_editorConfig;
    }

    /**
     * Save product design images
     *
     * @return GoMage_ProductDesigner_Model_Design
     * @throws Exception
     */
    public function saveProductDesignedImages()
    {
        $product = $this->initializeProduct();
        $images = Mage::app()->getRequest()->getParam('images');
        if ($product->getId() && $images && !empty($images)) {
            $design = Mage::getModel('gomage_designer/design')->saveDesign($product, $this->_getRequest()->getParams());
            return $design;
        } elseif(!$product->getId()) {
            throw new Exception(Mage::helper('gomage_designer')->__('Product is not defined'));
        } elseif(!$images || empty($images)) {
            throw new Exception(Mage::helper('gomage_designer')->__('Designed images are empty'));
        }
    }

    /**
     * Initialize current product from request
     *
     * @return Mage_Catalog_Model_Product
     */
    public function initializeProduct()
    {
        $product = Mage::registry('product');
        if ($product) {
            return $product;
        }
        $request = Mage::app()->getRequest();
        $productId = $request->getParam("id", false);

        $product = Mage::getModel('catalog/product');
        if ($productId) {
            $product->load($productId);
        }
        Mage::register('product', $product);

        return $product;
    }

    /**
     * Price calculation depending on product options
     *
     * @return array
     */
    public function getProductPriceConfig()
    {
        $config = array();
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->initializeProduct();
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency($tierPrice['website_price'], false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (int)$tierPrice['website_price'], true),
                false, false);
        }
        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object'=>$responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
                $config[$option] = $value;
            }
        }

        return $config;
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     *
     * @return string
     */
    public function getProductPriceConfigJson()
    {
        return Mage::helper('core')->jsonEncode($this->getProductPriceConfig());
    }

    /**
     * Return product design from request
     *
     * @param Mage_Catalog_Model_Product $product Product
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getProductDesign($product)
    {
        if (is_null($this->_productDesign)) {
            $designId = (int) Mage::app()->getRequest()->getParam('design_id', false);
            if ($designId) {
                $design = Mage::getModel('gomage_designer/design')->load($designId);
                if ($design->getId() && $design->getProductId() == $product->getId()) {
                    $this->_productDesign = $design;
                } else {
                    $this->_productDesign = false;
                }
            }
        }

        return $this->_productDesign;
    }
}
