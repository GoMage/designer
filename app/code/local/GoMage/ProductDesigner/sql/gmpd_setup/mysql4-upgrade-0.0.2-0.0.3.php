<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

if(!$installer->getAttribute('catalog_product', 'color', 'used_in_product_listing')) {
    $installer->updateAttribute('catalog_product', 'color', 'used_in_product_listing', '1');
}
if(!$installer->getAttributeId('catalog_product', 'size')) {
    $installer->addAttribute('catalog_product', 'size', array(
        'type'                      => 'int',
        'label'                     => 'Size',
        'input'                     => 'select',
        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'                   => true,
        'required'                  => false,
        'user_defined'              => true,
        'default'                   => 0,
        'searchable'                => false,
        'filterable'                => false,
        'comparable'                => false,
        'visible_on_front'          => false,
        'unique'                    => false,
        'apply_to'                  => 'simple,configurable',
        'used_in_product_listing'   => '1',
        'group'                     => 'General'
    ));
}
if(!$installer->getAttribute('catalog_product', 'size', 'used_in_product_listing')) {
    $installer->updateAttribute('catalog_product', 'size', 'used_in_product_listing', '1');
}
