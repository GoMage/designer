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

    public function getDesignPriceConfig()
    {
        $config = array(
            'fixed_price' => Mage::getStoreConfig('gmpd/general/fixed_price') ?:0,
            'text_price'  => Mage::getStoreConfig('gmpd/general/price_for_text')?:0,
            'image_text'  => Mage::getStoreConfig('gmpd/general/price_for_image')?:0
        );

        return $config;
    }

    public function getDesignPriceConfigJson()
    {
        return Zend_Json::encode($this->getDesignPriceConfig());
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getPriceJsonConfig()
    {
        $config = array();
        if (!$this->hasProductOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();
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

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasProductOptions()
    {
        if ($this->getProduct()->getTypeInstance(true)->hasOptions($this->getProduct())) {
            return true;
        }
        $this->getChildHtml();
        return false;
    }
}