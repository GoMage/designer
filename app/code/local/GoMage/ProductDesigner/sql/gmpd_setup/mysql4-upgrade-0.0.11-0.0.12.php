<?php
$installer = $this;
/**
 * @var $installer Mage_Core_Model_Resource_Setup
 * @var $category GoMage_ProductDesigner_Model_Clipart_Category
 * @var $clipart GoMage_ProductDesigner_Model_Clipart
 */

$installer->startSetup();

$category = Mage::getModel('gmpd/clipart_category');
$defaultCategory = $category->getDefaultCategory();

$categoryData = array(
    'parent_id' => $defaultCategory->getId(),
    'name' => 'Default Images',
    'sort_order' => 0,
    'is_active' => 0,
    'is_default' => 0,
    'level' => $defaultCategory->getLevel() + 1,
    'position' => 0
);
$category->setData($categoryData)->save();
$categoryId = $category->getId();

if($categoryId) {
    $clipartsDir = Mage::getSingleton('gmpd/clipart_gallery_config')->getBaseMediaPath() . '/';

    $pngImages = glob($clipartsDir .'*.png');
    $jpgImages = glob($clipartsDir .'*.jpg');
    $jpegImages = glob($clipartsDir .'*.jpeg');
    $gifImages = glob($clipartsDir .'*.gif');

    $defaultImages = array_merge($pngImages, $jpegImages, $jpgImages, $gifImages);

    $imageIndex = 0;
    foreach($defaultImages as $image) {
        $imageUrl = str_replace($clipartsDir, '/', $image);
        $clipart = Mage::getModel('gmpd/clipart');
        $clipart->setData(array(
            'category_id' => $categoryId,
            'label' => '',
            'image' => $imageUrl,
            'tags' => '',
            'position' => $imageIndex,
            'disabled' => 0,
        ));
        $clipart->save();
        $imageIndex++;
    }
}

$installer->endSetup();