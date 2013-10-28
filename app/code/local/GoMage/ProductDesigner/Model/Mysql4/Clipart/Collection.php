<?php
class GoMage_ProductDesigner_Model_Mysql4_Clipart_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('gmpd/clipart');
    }

    public function addVisibleCategoriesFilter() {
        /**
         * @var $categoryId int
         * @var $joinTableAlias string
         * @var $joinTable string
         * @var $select Varien_Db_Select
         */

        $select = $this->getSelect();
        $joinTableAlias = 'categories';
        $joinTable = $this->getTable(GoMage_ProductDesigner_Model_Clipart_Category::ENTITY);

        $select->joinInner(
            array($joinTableAlias => $joinTable),
            $joinTableAlias.'.category_id = main_table.category_id AND '.$joinTableAlias.'.is_active=1'
        );

        return $this;
    }

    public function addCategoryFilter($categoryId) {
        /**
         * @var $categoryId int
         * @var $joinTableAlias string
         * @var $joinTable string
         * @var $select Varien_Db_Select
         */

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

    public function addTagsFilter($tags) {
        /**
         * @var $tags string
         * @var $select Varien_Db_Select
         */

        $tableAlias = 'main_table';
        $select = $this->getSelect();
        $tags = (string) $tags;
        $tags = $this->prepareTags($tags);
        $select->where($tableAlias.'.label REGEXP '.$tags.'');
    }

    private function prepareTags($tags) {
        /**
         * @var $tags string
         * @var $tagsArray array
         * @var $readConnection Varien_Db_Adapter_Pdo_Mysql | Varien_Db_Adapter_Interface
         */

        $readConnection = $this->getResource()->getReadConnection();

        $tagsArray = explode(',', $tags);
        $tags = $readConnection->quote(implode('|', $tagsArray));
        return $tags;
    }
}