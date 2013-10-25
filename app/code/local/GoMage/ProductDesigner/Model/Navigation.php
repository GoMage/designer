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
    protected $_availableFilters = array('category');
    protected $_category;

    protected function _prepareProductCollection()
    {
        $category = $this->_getRootCategory();
        $mediaGalleryAttribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'media_gallery');
        $storeId = Mage::app()->getStore()->getId();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('type_id', array('in' => Mage::helper('designer')->getAllowedProductTypes()))
            ->addAttributeToFilter('enable_product_designer', 1)
            ->addStoreFilter(Mage::app()->getStore())
            ->addCategoryFilter($category)
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
            ->where('IFNULL(media_gallery_value.design_area, media_gallery_value_default.design_area) IS NOT NULL');

        }
        $collection->getSelect()->group('e.entity_id');

        return $collection;
    }

    protected function _getRootCategory()
    {
        if (is_null($this->_category)) {
            $categoryId = Mage::getStoreConfig('gmpd/navigation/category')
                ?: Mage::app()->getStore()->getRootCategoryId();
            $this->_category = Mage::getModel('catalog/category')->load($categoryId);
        }

        return $this->_category;
    }

    protected function _getAssociatedProductCollection($ids = array())
    {
        $collection = Mage::getResourceModel('catalog/product_type_configurable_product_collection');
        $collection->addCategoryIds();
        $this->_addFiltersAttributes($collection);
        if (!empty($ids)) {
            $collection->getSelect()->where("link_table.parent_id IN (?)", $ids);
        }
        $collection->getSelect()->group('e.entity_id');

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
    public function getFilterOptions($filter)
    {
        $collection = $this->_prepareProductCollection();
        $ids = $collection->getAllIds();
        $this->applyFilters($collection, $filter);
        $options = array();

        if ($filter == 'category') {
            $categoryIds = array();
            $availableCategories = $this->_getRootCategory()->getAllChildren();
            if (!$availableCategories) {
                return $options;
            }
            $availableCategories = explode(',', $availableCategories);

            foreach ($collection as $_item) {
                $categoryIds = array_merge($categoryIds, $_item->getCategoryIds());
            }
            $associatedProducts = $this->_getAssociatedProductCollection($ids);
            $this->applyFilters($associatedProducts, $filter);
            foreach ($associatedProducts as $_item) {
                $categoryIds = array_merge($categoryIds, $_item->getCategoryIds());
            }
            $categoryIds = array_unique($categoryIds);
            $categoryIds = array_intersect($availableCategories, $categoryIds);

            if (!empty($categoryIds)) {
                $categoryCollection = Mage::getModel('catalog/category')->getCollection();
                $categoryCollection->addFieldToFilter('entity_id', array('in' => $categoryIds))
                    ->addAttributeToSelect('name');
                foreach ($categoryCollection as $_category) {
                    $options[$_category->getId()] = $_category->getName();
                }
            }
        } else {
            foreach ($collection as $_item) {
                if (($value = $_item->getData($filter)) && ($label = $_item->getAttributeText($filter))) {
                    $options[$value] = $label;
                }
            }

            $associatedProducts = $this->_getAssociatedProductCollection($ids);
            $this->applyFilters($associatedProducts, $filter);
            foreach ($associatedProducts as $_item) {
                if (($value = $_item->getData($filter)) && ($label = $_item->getAttributeText($filter))) {
                    $options[$value] = $label;
                }
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
            $ids = $collection->getAllIds();

            $associatedProducts = $this->_getAssociatedProductCollection($ids);
            $this->applyFilters($associatedProducts);

            $parentIds = array();
            foreach ($associatedProducts as $_item) {
                $parentIds[] = $_item->getParentId();
            }

            $parentIds = array_unique($parentIds);
            $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addMinimalPrice()
                ->addFinalPrice();

            $this->applyFilters($collection);
            if (!empty($parentIds)) {
                $collection->getSelect()->orWhere("e.entity_id IN (?)", $parentIds);
            }

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
        return array_merge($this->_availableFilters, array(
            Mage::getStoreConfig('gmpd/navigation/color_attribute'),
            Mage::getStoreConfig('gmpd/navigation/size_attribute')
        ));
    }

    /**
     * Apply filters to collection
     *
     * @param Object $request Request
     * @return $this
     */
    public function applyFilters($collection, $excludeFilter = null)
    {
        $request = Mage::app()->getRequest();
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
