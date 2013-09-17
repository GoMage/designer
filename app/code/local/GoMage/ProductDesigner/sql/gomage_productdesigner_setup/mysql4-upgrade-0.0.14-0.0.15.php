<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
  -- ADD `session_id` FIELD TO DESIGN TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_design')}`
    ADD COLUMN `session_id` VARCHAR(40) NOT NULL DEFAULT '';
  -- ADD `create_time` FIELD TO DESIGN TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_design')}`
    ADD COLUMN `created_date` DATETIME NOT NULL;
SQL
);

$installer->endSetup();
