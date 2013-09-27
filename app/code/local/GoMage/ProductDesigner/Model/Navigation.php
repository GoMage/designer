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
    protected $_availableFilters = array('category', 'color', 'size');

    /**
     * Return product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_collection)) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('color')
                ->addAttributeToSelect('size')
                ->addAttributeToFilter('type_id', array('in' => Mage::helper('designer')->getAllowedProductTypes()))
                ->addAttributeToFilter('enable_product_designer', 1)
                ->addAttributeToFilter('design_areas', array('notnull' => true))
                ->addStoreFilter(Mage::app()->getStore())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addCategoryIds();

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

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
        return $this->_availableFilters;
    }

    /**
     * Apply filters to collection
     *
     * @param Object $request Request
     * @return $this
     */
    public function applyFilters($request)
    {
        $filters = $this->getAvailableFilters();
        foreach ($filters as $_filter) {
            if ($value = $request->getParam($_filter)) {
                $this->_applyFilter($_filter, $value);
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
