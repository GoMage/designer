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

     public function addDesignPriceToFinalPrice(Varien_Event_Observer $observer)
     {
         $product = $observer->getEvent()->getProduct();
         $qty = $observer->getEvent()->getQty();
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

     public function addDesignCustomOptionToProduct(Varien_Event_Observer $observer)
     {
        $buyRequest = $observer->getEvent()->getBuyRequest();
        $product = $observer->getEvent()->getProduct();

        if ($designId = $buyRequest->getDesign()) {
            $product->addCustomOption('design', $designId);
        }
     }
 }