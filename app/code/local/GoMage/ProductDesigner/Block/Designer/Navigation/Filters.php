<?php 
/**
 * GoMage.com extension for Magento
 *
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
 * GoMage ProductDesigner navigation filters block
 *
 * @todo       Add multiple filters fuctional
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Designer_Navigation_Filters extends Mage_Core_Block_Template
{
    protected $_productCollection = null;

    /**
     * Prepare layout
     * Apply filters to product collection
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->applyFilters();
    }

    /**
     * Apply filters to product collection
     *
     * @return void
     */
    public function applyFilters()
    {
        $request = $this->getRequest();
        $filters = $this->getAvailableFilters();
        $collection = $this->getProductCollection();

        foreach ($filters as $_filter) {
            if ($value = $request->getParam($_filter)) {
                Mage::getSingleton('gmpd/navigation')->applyFilter($collection, $_filter, $value);
            }
        }
    }

    /**
     * Return product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = Mage::getSingleton('gmpd/navigation')->getProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * Prepare category filter options
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $productCollection Product Collection
     * @return null|Mage_Catalog_Model_Mysql_Category_Collection
     */
    protected function _prepareCategoryFilters($productCollection)
    {
        $categoryIds = array();
        foreach ($productCollection as $_product) {
            $categoryIds = array_merge($categoryIds, $_product->getCategoryIds());
        }

        $categoryIds = array_unique($categoryIds);

        if (!empty($categoryIds)) {
            $categoryCollection = Mage::getModel('catalog/category')->getCollection();
            $categoryCollection->addFieldToFilter('entity_id', array('in' => $categoryIds))
                ->addAttributeToSelect('name');

            return $categoryCollection;
        }

        return null;
    }

    /**
     * Return available filter options
     *
     * @param string $filter Filter Name
     * @return Mage_Catalog_Model_Mysql_Category_Collection|null|Varien_Data_Collection
     */
    public function getAvailableFiltersOptions($filter)
    {
        $productCollection = $this->getProductCollection();
        if ($filter == 'category') {
            return $this->_prepareCategoryFilters($productCollection);
        }

        $filters = new Varien_Data_Collection();
        foreach($productCollection as $product) {
            if($value = $product->getData($filter)) {
                if ($filters->getItemById($value)) {
                    continue;
                }
                $item = new Varien_Object(array(
                    'id' => $value,
                    'name' => $product->getAttributeText($filter)
                ));
                $filters->addItem($item);
            }
        }

        return $filters;
    }

    /**
     * Return available filters
     *
     * @return array
     */
    public function getAvailableFilters()
    {
        return Mage::getSingleton('gmpd/navigation')->getAvailableFilters();
    }

    /**
     * Is filter option selected
     *
     * @param string $filter Filter Name
     * @param int    $value  Option value
     * @return bool
     */
    public function isFilterOptionSelected($filter, $value)
    {
        return $this->getRequest()->getParam($filter) == $value ? true : false;
    }
}
