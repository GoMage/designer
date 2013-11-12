<?php
//@todo remove this file
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
 class GoMage_ProductDesigner_Model_Observer
 {

    public function onPrepareProductSave(Varien_Event_Observer $observer)
    {
      $product = $observer->getProduct();
      $params  = $observer->getRequest()->getParam('product');
      $flag    = (int)$params["enable_product_designer"];

      $product->setEnableProductDesigner($flag);
    }

    public function addDesignEnabledToProducts(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        $collection->addAttributeToSelect('enable_product_designer', true);
    }

     /**
      * Add design option to quote item
      *
      * @param Varien_Event_Observer $observer Observer
      * @return void
      */
     public function addDesignToQuoteItem(Varien_Event_Observer $observer)
     {
         $item = $observer->getEvent()->getQuoteItem();
         if ($design = Mage::app()->getRequest()->getParam('design')) {
             $item->addOption(array(
                 "product_id" => $item->getProduct()->getId(),
                 "product" => $item->getProduct(),
                 "code" => "design",
                 "value" => $design
             ));
         }
     }

     /**
      * Add design Price to to final price
      *
      * @param Varien_Event_Observer $observer Observer
      * @return void
      */
     public function addDesignPriceToFinalPrice(Varien_Event_Observer $observer)
     {
         $product = $observer->getEvent()->getProduct();
         $buyRequest = $product->getCustomOption('info_buyRequest');
         if ($buyRequest) {
             $buyRequest = unserialize($buyRequest->getValue());
             if (isset($buyRequest['design'])) {
                 $designId = $buyRequest['design'];
                 $design = Mage::getModel('gmpd/design')->load($designId);
                 if ($design && $design->getId() && $design->getPrice() > 0) {
                     $finalPrice = $product->getData('final_price');
                     $finalPrice += $design->getPrice();
                     $product->setFinalPrice($finalPrice);
                 }
             }
         }
     }

     /**
      * Add Design custom option to product
      *
      * @param Varien_Event_Observer $observer Observer
      * @return void
      */
     public function addDesignCustomOptionToProduct(Varien_Event_Observer $observer)
     {
        $buyRequest = $observer->getEvent()->getBuyRequest();
        $product = $observer->getEvent()->getProduct();

        if ($designId = $buyRequest->getDesign()) {
            $design = Mage::getModel('gmpd/design')->load($designId);
            if ($design && $design->getId()) {
                if (!$this->_checkProductDesignColorMatch($product, $design, $buyRequest)) {
                    $product->setOptionsValidationFail(true);
                    $product->setDesignColorValidationFail(true);
                    Mage::throwException(
                        Mage::helper('designer')->__('Сonfiguration of your design does not match the configuration of the product')
                    );
                }
                $product->addCustomOption('design', $designId);
            }
        }
     }

     protected function _checkProductDesignColorMatch($product, $design, $buyRequest)
     {
         if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
             return true;
         }

         if ($color = $design->getColor()) {
             if ($colorAttribute = Mage::helper('designer')->hasColorAttribute()) {
                if ($superAttribute = $buyRequest->getSuperAttribute()) {
                    if (isset($superAttribute[$colorAttribute])) {
                        return $superAttribute[$colorAttribute] == $color;
                    }
                }
             }
         }

         return true;
     }

     public function loadAttribute($event)
     {
         $attribute = $event->getAttribute();
         $attribute_id = (int) $attribute->getAttributeId();
         $colorAttributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
         if (!$colorAttributeCode || $colorAttributeCode != $attribute->getAttributeCode()) {
             return;
         }
         $connection = Mage::getSingleton('core/resource')->getConnection('read');
         $data = array();

         $table = Mage::getSingleton('core/resource')->getTableName('gomage_productdesigner_attribute_option');

         $option_images = array();
         $_option_images = $connection->fetchAll("SELECT * FROM {$table} WHERE attribute_id = {$attribute_id}");

         foreach ($_option_images as $imageInfo) {
             $option_images[$imageInfo['option_id']] = $imageInfo;
         }
         $data['option_images'] = $option_images;

         if ($data && is_array($data) && ! empty($data)) {
             $attribute->addData($data);
         }
     }
 }