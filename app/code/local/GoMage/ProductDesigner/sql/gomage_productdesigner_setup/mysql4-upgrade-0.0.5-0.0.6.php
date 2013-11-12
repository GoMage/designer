<?php
/** @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();


//Install cliparts entities

$installer->addEntityType('clipart_image', array(
    'entity_model'          => 'gomage_designer/clipart',
    'table'                 => 'gomage_designer/clipart',
    'increment_per_store'   => false
));

$installer->addEntityType('clipart_category', array(
    'entity_model'          => 'gomage_designer/clipart_category',
    'table'                 => 'gomage_designer/category',
    'increment_per_store'   => false
));

$installer->endSetup();