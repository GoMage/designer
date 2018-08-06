<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.6.0
 * @since        Available since Release 1.0.0
 */

class GoMage_ProductDesigner_Model_Mysql4_Clipart_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('gomage_designer/clipart');
    }

    public function addVisibleCategoriesFilter()
    {
        $select = $this->getSelect();
        $joinTableAlias = 'categories';
        $joinTable = $this->getTable(GoMage_ProductDesigner_Model_Clipart_Category::ENTITY);

        $select->joinInner(
            array($joinTableAlias => $joinTable),
            $joinTableAlias.'.category_id = main_table.category_id AND '.$joinTableAlias.'.is_active=1'
        );

        return $this;
    }

    public function addCategoryFilter($categoryId)
    {
        $categoryId = (int) $categoryId;
        if($categoryId <= 0) return;

        $select = $this->getSelect();
        $joinTableAlias = 'categories';
        $joinTable = $this->getTable(GoMage_ProductDesigner_Model_Clipart_Category::ENTITY);

        $select->joinInner(
            array($joinTableAlias => $joinTable),
            $joinTableAlias.'.category_id = main_table.category_id
            AND '.$joinTableAlias.'.path REGEXP "/'.$categoryId.'[^0-9]|/'.$categoryId.'$"
            AND '.$joinTableAlias.'.is_active=1'
        );

        return $this;
    }

    public function addTagsFilter($tags)
    {
        $tableAlias = 'main_table';
        $select = $this->getSelect();
        $tags = (string) $tags;
        $tags = $this->_prepareTags($tags);
        $select->where($tableAlias.'.tags REGEXP '.$tags.'');
    }

    protected function _prepareTags($tags)
    {
        $readConnection = $this->getResource()->getReadConnection();

        $tagsArray = explode(',', str_replace(' ', '', $tags));
        $tags = $readConnection->quote(implode('|', $tagsArray));
        return $tags;
    }
}
