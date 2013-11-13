<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

try {
    /* Clipart category */

    $table = $installer->getConnection()->newTable($installer->getTable('gomage_designer/clipart_category'))
        ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), "Category Id")
        ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Parent Id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Name')
        ->addColumn('path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(), 'Path')
        ->addColumn('level', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
            'nullable' => false
        ), 'Level')
        ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => 0
        ), 'Position')
        ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
        ), 'Sort Order')
        ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'unsigned' => true,
            'default'  => 1
        ), 'Is Active');
    $installer->getConnection()->createTable($table);

    $installer->addEntityType('clipart_category', array(
        'entity_model'          => 'gomage_designer/clipart_category',
        'table'                 => 'gomage_designer/category',
        'increment_per_store'   => false
    ));

    Mage::getModel('gomage_designer/clipart_category')->setData(array(
        'category_id' => 1,
        'parent_category' => 0,
        'name' => 'Root Category',
        'sort_order' => 0,
        'is_active' => 0,
        'path' => 1,
        'level' => 0,
        'position' => 0
    ))->save();

    /* Clipart category end */

    /* Cliparts */

    $table = $installer->getConnection()->newTable($installer->getTable('gomage_designer/clipart'))
        ->addColumn('clipart_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), "Clipart Id")
        ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Category Id')
        ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
            'nullable' => false
        ), 'Entity Type Id')
        ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
        ), 'Position')
        ->addColumn('label', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Label')
        ->addColumn('image', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Image')
        ->addColumn('tags', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Tags')
        ->addColumn('disabled', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'unsigned' => true,
            'nullable' => false,
            'default'  => 0
        ), 'Disabled');
    $installer->getConnection()->createTable($table);

    $installer->addEntityType('clipart_image', array(
        'entity_model'          => 'gomage_designer/clipart',
        'table'                 => 'gomage_designer/clipart',
        'increment_per_store'   => false
    ));

    $category = Mage::getModel('gomage_designer/clipart_category');
    $category->setData(array(
        'parent_category' => GoMage_ProductDesigner_Model_Clipart_Category::TREE_ROOT_ID,
        'name' => 'Cliparts',
        'sort_order' => 0,
        'is_active' => 1,
        'position' => 0
    ))->save();

    addClipartsToCategory($category);

    /* Cliparts end*/

    /* Uploaded Images */

    $table = $installer->getConnection()->newTable($installer->getTable('gomage_designer/uploadedImage'))
        ->addColumn('image_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), "Image Id")
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Customer Id')
        ->addColumn('image', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Image')
        ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Session Id');
    $installer->getConnection()->createTable($table);

    /* Uploaded Images end */

    /* Fonts */

    $table = $installer->getConnection()->newTable($installer->getTable('gomage_designer/font'))
        ->addColumn('font_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), "Font Id")
        ->addColumn('label', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Label')
        ->addColumn('font', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Font')
        ->addColumn('disabled', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
            'unsigned' => true,
            'nullable' => false,
            'default'  => 0
        ), "Disabled")
        ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
        ), "Disabled");
    $installer->getConnection()->createTable($table);

    /* Fonts end */

    /* Designs */

    $table = $installer->getConnection()->newTable($installer->getTable('gomage_designer/design'))
        ->addColumn('design_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), "Font Id")
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
        ), "Customer Id")
        ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
            'nullable' => false,
        ), "Customer Id")
        ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'Comment')
        ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Session Id')
        ->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(), 'Created Date')
        ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
            'scale'     => 4,
            'precision' => 12,
        ), 'Design Price')
        ->addColumn('color', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
            'unsigned' => true,
        ), "Design color");
    $installer->getConnection()->createTable($table);

    /* Design end */

    /* Design Images */

    $table = $installer->getConnection()->newTable($installer->getTable('gomage_designer/design_image'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), "Image Id")
        ->addColumn('design_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), "Design Id")
        ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), "Product Id")
        ->addColumn('image_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), "Original Image Id")
        ->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), "Image")
        ->addColumn('layer', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), "Layer Image")
        ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
            'scale'     => 4,
            'precision' => 12,
        ), "Price")
        ->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(), "Price");
    $installer->getConnection()->createTable($table);

    $this->getConnection()->addForeignKey(
        $installer->getFkName('gomage_designer/design_image', 'design_id', 'gomage_designer/design', 'design_id'),
        $installer->getTable('gomage_designer/design_image'), 'design_id',
        $installer->getTable('gomage_designer/design'), 'design_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );
    /* Design Images end */

    /**
     * Enable/disable designer attribute
     */
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'enable_product_designer', array(
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

    /* Add Color field to product image */
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_attribute_media_gallery_value'),
        'color',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'Image Color'
        )
    );

    /* Add Design Area to image */
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_attribute_media_gallery_value'),
        'design_area',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Image Design Area'
        )
    );

    /* Update backend model for med */
    $this->updateAttribute(Mage_Catalog_Model_Product::ENTITY,
        'media_gallery',
        'backend_model',
        'gomage_designer/catalog_product_attribute_backend_media'
    );

    /* Add option table for attribute */

    $table = $installer->getConnection()->newTable($installer->getTable('gomage_designer/attribute_option'))
        ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), "Option Id")
        ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), "Attribute Id")
        ->addColumn('filename', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'File Name')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Name')
        ->addColumn('size', Varien_Db_Ddl_Table::TYPE_DECIMAL, 255, array(
            'nullable' => false,
            'scale'     => 4,
            'precision' => 12,
        ), 'Size');
    $installer->getConnection()->createTable($table);

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('gomage_designer/attribute_option', 'option_id', 'eav/attribute_option', 'option_id'),
        $installer->getTable('gomage_designer/attribute_option'), 'option_id',
        $installer->getTable('eav/attribute_option'), 'option_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );

    $installer->endSetup();
} catch (Exception $e) {
    Mage::logException($e);
}

function addClipartsToCategory($category)
{
    if($category && $category->getId()) {
        $clipartsDir = Mage::getSingleton('gomage_designer/clipart_gallery_config')->getBaseMediaPath() . '/';

        $pngImages = glob($clipartsDir .'*.png');
        $jpgImages = glob($clipartsDir .'*.jpg');
        $jpegImages = glob($clipartsDir .'*.jpeg');
        $gifImages = glob($clipartsDir .'*.gif');

        $defaultImages = array_merge($pngImages, $jpegImages, $jpgImages, $gifImages);

        $imageIndex = 0;
        foreach($defaultImages as $image) {
            $imagePath = str_replace($clipartsDir, '/', $image);
            $clipart = Mage::getModel('gomage_designer/clipart');
            $clipart->setData(array(
                'category_id' => $category->getId(),
                'label' => '',
                'image' => $imagePath,
                'tags' => '',
                'position' => $imageIndex,
                'disabled' => 0,
            ));
            $clipart->save();
            $imageIndex++;
        }
    }
}


