<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$defaultValue = $this->getEntityTypeId('clipart_image');

$installer->run(<<<SQL
  -- ADD `entity_type_id` FIELD TO CLIPARTS TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_clipart')}`
    ADD COLUMN `entity_type_id` INT(3) NOT NULL DEFAULT '{$defaultValue}';
SQL
);

$installer->endSetup();
