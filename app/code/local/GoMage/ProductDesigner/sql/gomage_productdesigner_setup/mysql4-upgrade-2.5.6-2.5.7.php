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

    $installer->getConnection()->dropColumn(
        $installer->getTable('gomage_productdesigner_attribute_option'),
        'name'
    );
    $installer->getConnection()->dropColumn(
        $installer->getTable('gomage_productdesigner_attribute_option'),
        'size'
    );

    $installer->getConnection()->addColumn(
        $installer->getTable('gomage_productdesigner_attribute_option'),
        'color_hex',
        "VARCHAR (6)"
    );

    $installer->endSetup();
} catch (Exception $e) {
    Mage::logException($e);
}
