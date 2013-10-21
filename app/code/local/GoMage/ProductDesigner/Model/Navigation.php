<?php
/**
 * GoMage.com extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the GoMage ProductDesigner module to newer versions in the future.
 * If you wish to customize the GoMage ProductDesigner module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @copyright  Copyright (C) 2013 GoMage.com (http://www.gomage.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GoMage ProductDesigner navigation model
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Model
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Model_Navigation extends Mage_Core_Model_Abstract
{
    protected $_collection = null;
    protected $_availableFilters = array();

    protected function _prepareProductCollection()
    {
        $mediaGalleryAttribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'media_gallery');
        $storeId = Mage::app()->getStore()->getId();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('type_id', array('in' => Mage::helper('designer')->getAllowedProductTypes()))
            ->addAttributeToFilter('enable_product_designer', 1)
            ->addStoreFilter(Mage::app()->getStore())
            ->addCategoryIds();
        $this->_addFiltersAttributes($collection);

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        if ($mediaGalleryAttribute->getId()) {
            $collection->getSelect()->joinInner(
                array('media_gallery' => $collection->getTable(Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media::GALLERY_TABLE)),
                "e.entity_id = media_gallery.entity_id AND media_gallery.attribute_id = {$mediaGalleryAttribute->getId()}",
                array()
            )->joinLeft(
                array('media_gallery_value' => $collection->getTable(Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media::GALLERY_VALUE_TABLE)),
                "media_gallery.value_id = media_gallery_value.value_id AND media_gallery_value.store_id = {$storeId}",
                array()
            )->joinLeft(
                array('media_gallery_value_default' => $collection->getTable(Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media::GALLERY_VALUE_TABLE)),
                "media_gallery.value_id = media_gallery_value_default.value_id AND media_gallery_value_default.store_id = 0",
                array('media_gallery_value_default.design_area')
            )
            ->where('IFNULL(media_gallery_value.design_area, media_gallery_value_default.design_area) IS NOT NULL')
            ->group('e.entity_id');
        }

        return $collection;
    }

    protected function _addFiltersAttributes($collection)
    {
        $colorAttributeCode = Mage::getStoreConfig('gmpd/navigation/color_attribute');
        $colorAttribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $colorAttributeCode);
        $sizeAttributeCode = Mage::getStoreConfig('gmpd/navigation/size_attribute');
        $sizeAttribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $sizeAttributeCode);

//        if ($colorAttribute && $colorAttribute->getId()) {
//            $collection->joinLeft(
//                array('color' => $colorAttribute->getBackendTable()),
//                "color.attribute_id = {$colorAttribute->getId()} AND color.entity_id = e.entity_id",
//                array('color' => 'color.value_id')
//            );
//        }
//        if ($colorAttribute && $colorAttribute->getId()) {
//            $collection->joinLeft(
//                array('color' => $colorAttribute->getBackendTable()),
//                "color.attribute_id = {$colorAttribute->getId()} AND color.entity_id = e.entity_id",
//                array('color' => 'color.value_id')
//            );
//        }
        if ($colorAttribute && $colorAttribute->getId()) {
            $collection->addAttributeToSelect($colorAttributeCode, true);
        }

        if ($sizeAttribute && $sizeAttribute->getId()) {
            $collection->addAttributeToSelect($sizeAttributeCode, true);
        }
    }

    /**
     * @param string $filter Filter
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection Collection
     */
    public function getFilterOptions($filter, $request)
    {
        $collection = $this->_prepareProductCollection();
        $this->applyFilters($collection, $request, $filter);

        $options = array();
        foreach ($collection as $_item) {
            if (($value = $_item->getData($filter)) && ($label = $_item->getAttributeText($filter))) {
                $options[$value] = $label;
            }
        }
        $ids = $collection->getAllIds();
        $configurableCollection = Mage::getResourceModel('catalog/product_type_configurable_product_collection');
        $this->_addFiltersAttributes($configurableCollection);
        $configurableCollection->getSelect()->where("link_table.parent_id IN (?)", $ids);
        $this->applyFilters($configurableCollection, $request, $filter);
        foreach ($configurableCollection as $_item) {
            if (($value = $_item->getData($filter)) && ($label = $_item->getAttributeText($filter))) {
                $options[$value] = $label;
            }
        }

        return $options;
    }

    /**
     * Return product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_collection)) {

            $collection = $this->_prepareProductCollection();
            $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addMinimalPrice()
                ->addFinalPrice();
            $collection->getSelect()->joinLeft(
                array('link_table' => $collection->getTable('catalog/product_super_link')),
                "link_table.parent_id = e.entity_id",
                array()
            );
            $this->applyFilters($collection, Mage::app()->getRequest());
            $this->_collection = $collection;
        }

        return $this->_collection;
    }

    /**
     * Return available filters
     *
     * @return array
     */
    public function getAvailableFilters()
    {
        return array(
            Mage::getStoreConfig('gmpd/navigation/color_attribute'),
            Mage::getStoreConfig('gmpd/navigation/size_attribute')
        );
    }

    /**
     * Apply filters to collection
     *
     * @param Object $request Request
     * @return $this
     */
    public function applyFilters($collection, $request, $excludeFilter = null)
    {
        $filters = $this->getAvailableFilters();
        foreach ($filters as $_filter) {
            if ($value = $request->getParam($_filter)) {
                if ($excludeFilter === null || $excludeFilter != $_filter) {
                    $this->applyFilter($collection, $_filter, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Apply filter to collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection Collection
     * @param string                                         $filter     Filter
     * @param int|string                                     $value      Value
     * @return $this
     */
    public function applyFilter($collection, $filter, $value)
    {
        if ($filter == 'category') {
            $category = Mage::getModel('catalog/category')->load((int) $value);
            if ($category && $category->getId()) {
                $collection->addCategoryFilter($category);
            }
        } else {
            $collection->addFieldToFilter($filter, $value);
        }

        return $this;
    }
}
