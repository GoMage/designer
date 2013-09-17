<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
  -- ADD `image_id` FIELD TO DESIGN TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_design')}`
    ADD COLUMN `image_id` INT(11) NOT NULL DEFAULT '0';
SQL
);

$installer->endSetup();
