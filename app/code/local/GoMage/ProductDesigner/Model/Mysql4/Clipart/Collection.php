<?php
class GoMage_ProductDesigner_Model_Mysql4_Clipart_Collection extends GoMage_ProductDesigner_Model_Mysql4_Collection_Abstract {
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix              = 'designer_clipart_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject              = 'designer_clipart_collection';

    protected function _construct()
    {
        $this->_init('gmpd/clipart');
        $this
            ->addFilterToMap('entity_id', 'main_table.entity_id');
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