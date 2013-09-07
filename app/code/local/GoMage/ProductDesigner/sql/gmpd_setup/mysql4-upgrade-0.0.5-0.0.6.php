<?php
/** @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();


//Install cliparts entities

$installer->addEntityType('clipart_image', array(
    'entity_model'          => 'gmpd/clipart',
    'table'                 => 'gmpd/clipart',
    'increment_per_store'   => false
));

$installer->addEntityType('clipart_category', array(
    'entity_model'          => 'gmpd/clipart_category',
    'table'                 => 'gmpd/category',
    'increment_per_store'   => false
));

$installer->endSetup();