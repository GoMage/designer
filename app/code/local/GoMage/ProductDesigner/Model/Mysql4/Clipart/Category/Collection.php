<?php
class GoMage_ProductDesigner_Model_Mysql4_Clipart_Category_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix              = 'designer_clipart_category_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject              = 'designer_clipart_category_collection';

    protected function _construct()
    {
        $this->_init('gmpd/clipart_category');
        $this
            ->addFilterToMap('entity_id', 'main_table.entity_id');
    }

    public function addIdFilter($categoryIds)
    {
        if (is_array($categoryIds)) {
            if (empty($categoryIds)) {
                $condition = '';
            } else {
                $condition = array('in' => $categoryIds);
            }
        } elseif (is_numeric($categoryIds)) {
            $condition = $categoryIds;
        } elseif (is_string($categoryIds)) {
            $ids = explode(',', $categoryIds);
            if (empty($ids)) {
                $condition = $categoryIds;
            } else {
                $condition = array('in' => $ids);
            }
        }
        $this->addFieldToFilter('category_id', $condition);
        return $this;
    }

    public function addVisibleFilter() {
        $this->addFieldToFilter('is_active', 1);
        return $this;
    }
}