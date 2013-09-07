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
//@todo Implement universal methods for all filters (category, color, size and others)
class GoMage_ProductDesigner_Block_Product_Navigator extends Mage_Core_Block_Template {

    /**
     * @var array|Mage_Catalog_Model_Resource_Category_Collection
     */
    protected $availableCategories = array();
    protected $availableColors = array();
    protected $availableSizes = array();

    /**
     * Returns collection of available categories or empty array
     *
     * @return array|Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getAvailableCategories() {
        /**
         * @var $navigationListBlock    GoMage_ProductDesigner_Block_Product_List
         */
        if(empty($this->availableCategories)) {
            $this->availableCategories = array();
            $navigationListBlock = $this->getChild('productNavigatorList');
            $this->prepareNavigationListBlock($navigationListBlock);
            $availableCategories = $navigationListBlock->getAvailableCategories();
            $this->availableCategories = $availableCategories;
        }
        return $this->availableCategories;
    }

    public function getAvailableColors() {
        /**
         * @var $navigationListBlock    GoMage_ProductDesigner_Block_Product_List
         */
        if(empty($this->availableColors)) {
            $this->availableColors = array();
            $navigationListBlock = $this->getChild('productNavigatorList');
            $availableColors = $navigationListBlock->getAvailableColor();
            $this->availableColors = $availableColors;
        }
        return $this->availableColors;
    }

    public function getAvailableSizes() {
        /**
         * @var $navigationListBlock    GoMage_ProductDesigner_Block_Product_List
         */
        if(empty($this->availableSizes)) {
            $this->availableSizes = array();
            $navigationListBlock = $this->getChild('productNavigatorList');
            $availableSizes = $navigationListBlock->getAvailableSize();
            $this->availableSizes = $availableSizes;
        }
        return $this->availableSizes;
    }

    public function prepareNavigationListBlock($navigationListBlock)
    {
        $request = $this->getRequest();
        $categoryId = $request->getParam('category_id');
        $color = $request->getParam('color');
        $size = $request->getParam('size');
        /**
         * @var $navigationListBlock    GoMage_ProductDesigner_Block_Product_List
         */
        $navigationListBlock->setSelectedCategoryId($categoryId)
            ->setSelectedColor($color)
            ->setSelectedSize($size)
            ->getLoadedProductCollection();
    }


    /**
     * Adds categories to category select block and returns html
     *
     * @return string
     */
    public function getCategoriesSelect() {
        /**
         * @var $category   Mage_Catalog_Model_Product
         * @var $select     Mage_Core_Block_Html_Select
         */
        $categories = $this->getAvailableCategories();
        $select = $this->createCategoriesSelect();
        foreach($categories as $category) {
            $params = array();
            if($this->getSelectedCategoryId()
                && $category->getId() == $this->getSelectedCategoryId()
            ) {
                $params['selected'] = 'selected';
            }
            $select->addOption($category->getId(), $category->getName(), $params);
        }
        return $select->toHtml();
    }

    public function getColorSelect() {
        /**
         * @var $color      Varien_Object
         * @var $select     Mage_Core_Block_Html_Select
         */
        $colors = $this->getAvailableColors();
        $select = $this->createColorSelect();
        foreach($colors as $color) {
            $params = array();
            if($this->getSelectedColor()
                && $color->getId() == $this->getSelectedColor()
            ) {
                $params['selected'] = 'selected';
            }
            $select->addOption($color->getId(), $color->getName(), $params);
        }
        return $select->toHtml();
    }

    public function getSizeSelect() {
        /**
         * @var $size       Varien_Object
         * @var $select     Mage_Core_Block_Html_Select
         */
        $sizes = $this->getAvailableSizes();
        $select = $this->createSizeSelect();
        foreach($sizes as $size) {
            $params = array();
            if($this->getSelectedSize()
                && $size->getId() == $this->getSelectedSize()
            ) {
                $params['selected'] = 'selected';
            }
            $select->addOption($size->getId(), $size->getName(), $params);
        }
        return $select->toHtml();
    }

    /**
     * Creates and prepares category select block
     *
     * @return Mage_Core_Block_Html_Select
     */
    public function createCategoriesSelect() {
        /* @var $select     Mage_Core_Block_Html_Select */
        $select = $this->getLayout()->createBlock('core/html_select');
        $select->setName('category_id');
        $select->setId('categories_selector');
        $select->setClass('filter_selector');
        $select->addOption('', $this->__('Select Category'));
        return $select;
    }

    public function createColorSelect() {
        /* @var $select     Mage_Core_Block_Html_Select */
        $select = $this->getLayout()->createBlock('core/html_select');
        $select->setName('color');
        $select->setId('colors_selector');
        $select->setClass('filter_selector');
        $select->addOption('', $this->__('Select Color'));
        return $select;
    }
    public function createSizeSelect() {
        /* @var $select     Mage_Core_Block_Html_Select */
        $select = $this->getLayout()->createBlock('core/html_select');
        $select->setName('size');
        $select->setId('sizes_selector');
        $select->setClass('filter_selector');
        $select->addOption('', $this->__('Select Size'));
        return $select;
    }

    public function getFiltersHtml()
    {
        $filters = $this->getChild('navigation.filters');
        $filters->setCategories($this->getCategoriesSelect())
            ->setColors($this->getColorSelect())
            ->setSizes($this->getSizeSelect());

        return $filters->toHtml();
    }

    public function getProductListHtml()
    {
        $productList = $this->getChild('productNavigatorList');
        $this->prepareNavigationListBlock($productList);

        return $productList->toHtml();
    }
}