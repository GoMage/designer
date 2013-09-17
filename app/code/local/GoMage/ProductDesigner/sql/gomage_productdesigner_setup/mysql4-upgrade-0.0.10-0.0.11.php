<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
  -- ADD `disabled` FIELD TO FONTS TABLE
  -- ADD `position` FIELD TO FONTS TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_font')}`
    ADD COLUMN `disabled` INT(1) NOT NULL DEFAULT '0',
    ADD COLUMN `position` INT(6) NOT NULL DEFAULT '0';
SQL
);

$installer->endSetup();