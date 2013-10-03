<?php
/**
 * GoMage.com extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the GoMage ProductDesigner module to newer versions in the future.
 * If you wish to customize the GoMage ProductDesigner module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @copyright  Copyright (C) 2013 GoMage.com (http://www.gomage.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @var $this Mage_Eav_Model_Entity_Setup
 */

$installer = $this;
$installer->startSetup();

try {
    $table = $installer->getConnection()->newTable($installer->getTable('gmpd/attribute_option'))
        ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), "Option Id")
        ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), "Attribute Id")
        ->addColumn('filename', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'File Name')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Name')
        ->addColumn('size', Varien_Db_Ddl_Table::TYPE_DECIMAL, 255, array(
            'nullable' => false,
            'scale'     => 4,
            'precision' => 12,
        ), 'Size');
    $installer->getConnection()->createTable($table);

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('gmpd/attribute_option', 'option_id', 'eav/attribute_option', 'option_id'),
        $installer->getTable('gmpd/attribute_option'), 'option_id',
        $installer->getTable('eav/attribute_option'), 'option_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();
