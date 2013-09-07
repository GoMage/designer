<?php
/**
 * GoMage.com
 *
 * GoMage ProductDesigner Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */
class GoMage_ProductDesigner_Block_Product_List extends Mage_Catalog_Block_Product_List {

    //@todo remove available filters hardcode and make setting in backend
    /**
     * @var array
     */
    private $availableProductTypes = array('simple', 'configurable');
    private $availableFilters = array('category', 'color', 'size');

    /**
     * Returns product collection.
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection() {
        /* @var $collection     Mage_Catalog_Model_Resource_Product_Collection */
        if(is_null($this->_productCollection)) {
            $collection = $this->getPreparedCollection();
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }

    public function getAvailableFilters() {
        return $this->availableFilters;
    }

    /**
     * Returns prepared collection of products where product designer is enabled.
     * Prepares available categories for this products.
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getPreparedCollection() {
        /**
         * @var $productModel   Mage_Catalog_Model_Product
         * @var $collection     Mage_Catalog_Model_Resource_Product_Collection
         */
        $visibility = array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
        );

        $productModel = Mage::getModel('catalog/product');
        $collection = $productModel->getResourceCollection();
        $collection
            ->addAttributeToFilter('visibility', $visibility)
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('type_id', array('in'=>$this->availableProductTypes))
            ->addAttributeToFilter('enable_product_designer', 1)
            ->addAttributeToSelect('color')
            ->addAttributeToSelect('size')
            ->addStoreFilter(Mage::app()->getStore());

        $this->prepareFiltersAndApplyToCollection($collection);

        $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addAttributeToSelect('description')
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addCategoryIds();

        return $collection;
    }

    protected function prepareFiltersAndApplyToCollection($collection) {
        foreach($this->getAvailableFilters() as $filterName) {
            if($filterName == 'category') {
                $this->prepareCollectionOfAvailableCategories($collection);
                $this->applyCategoryFilterToCollection($collection);
            } else {
                $this->prepareCollectionOfAvailableFilterValues($filterName, $collection);
                $this->applyFilterToCollection($filterName, $collection);
            }
        }
    }

    /**
     * Prepares available categories for product's collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    public function prepareCollectionOfAvailableCategories(Mage_Catalog_Model_Resource_Product_Collection $collection) {
        /**
         * @var $collection             Mage_Catalog_Model_Resource_Product_Collection
         * @var $product                Mage_Catalog_Model_Product
         * @var $categoryModel          Mage_Catalog_Model_Category
         * @var $categoryCollection     Mage_Catalog_Model_Resource_Category_Collection
         */
        if(!$this->getAvailableCategories()) {
            $tempCollection = clone $collection;
            $categoryIds = array();
            $categoryCollection = new Varien_Data_Collection();

            foreach($tempCollection as $product) {
                foreach($product->getCategoryIds() as $categoryId) {
                    $categoryId = (int) $categoryId;
                    if($categoryId > 0 && !isset($categoryIds[$categoryId])) {
                        $categoryIds[$categoryId] = $categoryId;
                    }
                }
            }
            if(count($categoryIds) > 0) {
                $categoryModel = Mage::getModel('catalog/category');
                $categoryCollection = $categoryModel->getResourceCollection();
                $categoryCollection
                    ->addFieldToFilter('entity_id', array('in' => $categoryIds))
                    ->addAttributeToSelect('name');

            }
            $this->setAvailableCategories($categoryCollection);
        }
    }

    protected function prepareCollectionOfAvailableFilterValues($filterName, $collection) {
        /**
         * @var $collection             Mage_Catalog_Model_Resource_Product_Collection
         * @var $product                Mage_Catalog_Model_Product
         * @var $valuesCollection       Varien_Data_Collection
         * @var $valueItem              Varien_Object
         */
        if(!$this->getData('available_'.$filterName)) {
            $tempCollection = clone $collection;

            $valuesCollection = new Varien_Data_Collection();
            foreach($tempCollection as $product) {
                if($product->getData($filterName)) {
                    $valueItem = new Varien_Object();
                    $valueItem->setData(array(
                        'id' => $product->getData($filterName),
                        'name' => $product->getAttributeText($filterName)
                    ));
                    $valuesCollection->addItem($valueItem);
                }
            }
            $this->setData('available_'.$filterName, $valuesCollection);
            unset($tempCollection);
        }
    }

    protected function applyCategoryFilterToCollection($collection) {
        $selectedCategoryId = (int)$this->getSelectedCategoryId();

        if($selectedCategoryId > 0) {
            $categoryModel = Mage::getModel('catalog/category');
            $selectedCategory = $categoryModel->load($selectedCategoryId);
            $collection->addCategoryFilter($selectedCategory);
        }
    }

    protected function applyFilterToCollection($filterName, $collection) {
        $selectedFilterValue = $this->getData('selected_'.$filterName);
        if($selectedFilterValue) {
            $collection->addAttributeToFilter($filterName, $selectedFilterValue);
        }
    }

    public function getToolbarHtml() {
        /* @var $toolbarBlock Mage_Catalog_Block_Product_List_Toolbar */
        $toolbarBlock = $this->getChild('product_navigator_toolbar');
        if($toolbarBlock) {
            return $toolbarBlock->setCollection($this->getPreparedCollection())->toHtml();
        }
    }
}