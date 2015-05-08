<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.1.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Model_Observer
{

    public function onPrepareProductSave(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $params  = $observer->getRequest()->getParam('product');
        if (isset($params['enable_product_designer'])) {
            $product->setEnableProductDesigner((int)$params["enable_product_designer"]);
        }
    }

    public function addDesignEnabledToProducts(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('gomage_designer')->isEnabled()) {
            return;
        }
        $collection = $observer->getEvent()->getCollection();
        $collection->addAttributeToSelect('enable_product_designer');
    }

    /**
     * Add design Price to to final price
     *
     * @param Varien_Event_Observer $observer Observer
     * @return void
     */
    public function addDesignPriceToFinalPrice(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('gomage_designer')->isEnabled()) {
            return;
        }
        $product = $observer->getEvent()->getProduct();
        $design  = $product->getCustomOption('design');

        if ($design && $design->getValue()) {
            $design = Mage::getModel('gomage_designer/design')->load($design->getValue());
            if ($design && $design->getId() && $design->getPrice() > 0) {
                $finalPrice = $product->getData('final_price');
                $finalPrice += $design->getPrice();
                $product->setFinalPrice($finalPrice);
            }
        }
    }

    /**
     * @param  Varien_Object $buyRequest
     * @return int
     */
    protected function _getDesignIdByRequest(Varien_Object $buyRequest)
    {
        $design_id = 0;
        $options   = $buyRequest->getOptions();
        if (is_array($options) && isset($options[GoMage_ProductDesigner_Model_Design::CUSTOM_OPTION_ID])) {
            $design_id = reset($options[GoMage_ProductDesigner_Model_Design::CUSTOM_OPTION_ID]);
        }
        return (int)$design_id;
    }

    /**
     * Add Design custom option to product
     *
     * @param Varien_Event_Observer $observer Observer
     * @return void
     */
    public function addDesignCustomOptionToProduct(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('gomage_designer')->isEnabled()) {
            return;
        }

        $product    = $observer->getEvent()->getProduct();
        $buyRequest = $observer->getEvent()->getBuyRequest();

        $design_id = $this->_getDesignIdByRequest($buyRequest);

        if ($design_id > 0) {
            $design = Mage::getModel('gomage_designer/design')->load($design_id);
            if ($design && $design->getId()) {
                if (!$this->_checkProductDesignColorMatch($product, $design, $buyRequest)) {
                    $product->setOptionsValidationFail(true);
                    $product->setDesignColorValidationFail(true);
                    Mage::throwException(
                        Mage::helper('gomage_designer')->__('Сonfiguration of your design does not match the configuration of the product')
                    );
                }
                $product->addCustomOption('design', $design_id);
            }
        }
    }

    protected function _checkProductDesignColorMatch($product, $design, $buyRequest)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return true;
        }

        if ($color = $design->getColor()) {
            if ($colorAttribute = Mage::helper('gomage_designer')->hasColorAttribute()) {
                if ($superAttribute = $buyRequest->getSuperAttribute()) {
                    if (isset($superAttribute[$colorAttribute])) {
                        return $superAttribute[$colorAttribute] == $color;
                    }
                }
            }
        }

        return true;
    }

    public function loadAttribute(Varien_Event_Observer $event)
    {
        if (Mage::helper('gomage_designer')->advancedNavigationEnabled()) {
            return;
        }
        $attribute          = $event->getAttribute();
        $attribute_id       = (int)$attribute->getAttributeId();
        $colorAttributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
        if (!$colorAttributeCode || $colorAttributeCode != $attribute->getAttributeCode()) {
            return;
        }
        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $data       = array();

        $table = Mage::getSingleton('core/resource')->getTableName('gomage_productdesigner_attribute_option');

        $option_images  = array();
        $_option_images = $connection->fetchAll("SELECT * FROM {$table} WHERE attribute_id = {$attribute_id}");

        foreach ($_option_images as $imageInfo) {
            $option_images[$imageInfo['option_id']] = $imageInfo;
        }
        $data['option_images'] = $option_images;

        if ($data && is_array($data) && !empty($data)) {
            $attribute->addData($data);
        }
    }

    /**
     * @param Varien_Event_Observer $event
     */
    public function renameDesignImages(Varien_Event_Observer $event)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $event->getEvent()->getOrder();
        $items = $order->getAllVisibleItems();

        $number = 1;
        foreach ($items as $item) {
            $options = $item->getProductOptionByCode('options');

            foreach ($options as $option) {
                if (isset($option['option_id']) && $option['option_id'] == GoMage_ProductDesigner_Model_Design::CUSTOM_OPTION_ID) {
                    $design_id     = (int)$option['option_value'];
                    $design_images = Mage::getModel('gomage_designer/design')->getImages($design_id);
                    foreach ($design_images as $design_image) {
                        $design_image->renameImage($this->_getDesignImageFileName($order->getIncrementId(), $number++))
                            ->renameLayer($this->_getDesignImageFileName($order->getIncrementId(), $number++))
                            ->save();
                    }
                }
            }
        }
    }

    /**
     * @param string $name
     * @param int $number
     * @return string
     */
    protected function _getDesignImageFileName($name, $number = 1)
    {
        if ($number > 1) {
            return sprintf('%s(%s)', $name, $number);
        }
        return $name;
    }

    static public function checkK(Varien_Event_Observer $event)
    {
        $key = Mage::getStoreConfig('gomage_activation/designer/key');
        Mage::helper('gomage_designer')->a($key);
    }
}
