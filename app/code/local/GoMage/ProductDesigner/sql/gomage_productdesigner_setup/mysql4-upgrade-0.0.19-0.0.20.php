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
    $this->getConnection()->addForeignKey(
        $installer->getFkName('gomage_designer/design_image', 'design_id', 'gomage_designer/design', 'design_id'),
        $installer->getTable('gomage_designer/design_image'), 'design_id',
        $installer->getTable('gomage_designer/design'), 'design_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();
