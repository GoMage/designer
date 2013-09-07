<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
  -- ADD DEFAULT CLIPARTS CATEGORIES
    INSERT INTO `{$this->getTable('gomage_productdesigner_category')}` (`category_id`,`parent_id`, `name`, `is_active`, `is_default`)
    VALUES('1', '0', 'Root Category', '0', '1'), ('2', '1', 'Default Category', '1', '1');
SQL
);

$installer->endSetup();
