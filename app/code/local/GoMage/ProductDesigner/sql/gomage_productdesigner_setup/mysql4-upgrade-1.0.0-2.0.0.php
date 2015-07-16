<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.2.0
 * @since        Available since Release 2.0.0
 */

$installer = $this;

try {
    $installer->startSetup();

    $installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY,
        'enable_product_designer',
        'used_in_product_listing',
        1
    );

    $installer->endSetup();
} catch (Exception $e) {
    Mage::logException($e);
} catch (Mage_Core_Exception $e) {
    Mage::logException($e);
}