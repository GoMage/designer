<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
  -- ADD `disabled` FIELD TO CLIPARTS TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_clipart')}`
    ADD COLUMN `disabled` INT(1) NOT NULL DEFAULT '0';
SQL
);

$installer->endSetup();