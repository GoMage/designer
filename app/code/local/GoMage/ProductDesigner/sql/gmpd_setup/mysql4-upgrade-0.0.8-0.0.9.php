<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
  -- ADD `path` FIELD TO CLIPARTS CATEGORY TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_category')}`
    ADD COLUMN `path` VARCHAR(100) NOT NULL DEFAULT '';

    -- ADD `level` FIELD TO CLIPARTS CATEGORY TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_category')}`
    ADD COLUMN `level` INT(4) NOT NULL DEFAULT '0';

    -- ADD `position` FIELD TO CLIPARTS CATEGORY TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_category')}`
    ADD COLUMN `position` INT(4) NOT NULL DEFAULT '0';
SQL
);

$installer->endSetup();