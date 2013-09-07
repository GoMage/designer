<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

/**
 * Enable/disable product designer for selected product
 */
$installer->addAttribute('catalog_product', 'enable_product_designer', array(
    'type'              => 'int',
    'label'             => 'Enable Product Designer',
    'input'             => 'boolean',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => true,
    'default'           => 0,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'simple,configurable',
    'group'             => 'General'
));

/**
 * Design areas settings for selected product
 */
$installer->addAttribute('catalog_product', 'design_areas', array(
    'type'              => 'text',
    'label'             => 'Design area settings',
    'input'             => 'text',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'simple,configurable',
    'group'             => 'General'
));