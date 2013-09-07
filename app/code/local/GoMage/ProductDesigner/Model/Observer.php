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
 }