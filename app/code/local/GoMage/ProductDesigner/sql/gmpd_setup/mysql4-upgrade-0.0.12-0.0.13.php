<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
  -- ADD `session_id` FIELD TO UPLOADED IMAGE TABLE
    ALTER TABLE `{$this->getTable('gomage_productdesigner_uploaded_image')}`
    ADD COLUMN `session_id` VARCHAR(40) NOT NULL DEFAULT '';
SQL
);

$installer->endSetup();
