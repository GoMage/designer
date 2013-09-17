<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
    -- ADD NECESSARY FIELDS FOR DEFAULT CATEGORIES
    UPDATE `{$this->getTable('gomage_productdesigner_category')}`
    SET `path`='1', `level`='0' WHERE `category_id`=1;
    UPDATE `{$this->getTable('gomage_productdesigner_category')}`
    SET `path`='1/2', `level`='1' WHERE `category_id`=2;
SQL
);

$installer->endSetup();