<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2017 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.5.0
 * @since        Available since Release 2.0.0
 */

/* @var $installer GoMage_ProductDesigner_Model_Resource_Setup */
$installer = $this;

try {
    $installer->startSetup();

    /* Add Design Area to image */
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_attribute_media_gallery_value'),
        'canvas_background_file',
        "VARCHAR (255) DEFAULT NULL"
    );

    $installer->endSetup();
} catch (Exception $e) {
    Mage::logException($e);
} catch (Mage_Core_Exception $e) {
    Mage::logException($e);
}
