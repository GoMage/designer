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
 * GoMage ProductDesigner design filters block
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Designer_Design_Filters extends Mage_Core_Block_Template
{
    protected $_cliparts;
    /**
     * Return clipart collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Clipart_Collection
     */
    protected function _getClipartCollection()
    {
        if (is_null($this->_cliparts)) {
            $cliparts = Mage::getSingleton('gmpd/clipart')->getCliparts();
        }
    }

    /**
     * Return clipart categories collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Clipart_Category_Collection
     */
    public function getCategoriesCollection()
    {
        $category = Mage::getSingleton('gmpd/clipart_category');
        $defaultCategoryId = $category->getDefaultCategoryId();

        $categoriesCollection = $category->getCollection()
            ->addVisibleFilter()
            ->addFieldToFilter('parent_id', $defaultCategoryId);

        return $categoriesCollection;
    }

    /**
     * Return clipart subcategories collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Clipart_Category_Collection
     */
    public function getSubCategoriesCollection()
    {
        $categoryId = $this->_getSelectedCategoryId();
        $subCategoriesCollection = new Varien_Data_Collection();
        if($categoryId) {
            $category = Mage::getSingleton('gmpd/clipart_category')->load($categoryId);
            if ($category->getId()) {
                $subCategoriesCollection = $category->getCollection()
                    ->addVisibleFilter()
                    ->addFieldToFilter('path', array('like' => $category->getPath(). '/%'));
            }

        }

        return $subCategoriesCollection;
    }

    /**
     * Return selected category id
     *
     * @return int
     */
    protected function _getSelectedCategoryId()
    {
        return $this->getRequest()->getParam('mainCategory');
    }

    /**
     * Return selected subcategory Id
     *
     * @return int
     */
    protected function _getSelectedSubcategoryId()
    {
        return $this->getRequest()->getParam('subCategory');
    }

    /**
     * Return entered search tags
     *
     * @return string
     */
    protected function _getSearchTags()
    {
        return strip_tags($this->getRequest()->getParam('tags'));
    }

    public function navigationEnabled()
    {
        return (bool) Mage::getStoreConfig('gmpd/design/navigation');
    }

    public function searchEnabled()
    {
        return (bool) Mage::getStoreConfig('gmpd/design/search');
    }

    protected function _toHtml()
    {
        if (!$this->navigationEnabled() && !$this->searchEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
