<?php
class GoMage_ProductDesigner_Model_Mysql4_Clipart_Category_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('gomage_designer/clipart_category');
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

    public function addVisibleFilter()
    {
        $this->addFieldToFilter('is_active', 1);
        return $this;
    }

    public function addClipartCountFilter()
    {
        $concatExpr = $this->getConnection()->getConcatSql(array('main_table.path', $this->_conn->quote('/%')));
        $countSelect = $this->getConnection()->select()
            ->from(array('clipart_category' => $this->getMainTable()), null)
            ->joinLeft(
                array('cliparts' => $this->getTable('gomage_designer/clipart')),
                'clipart_category.category_id=cliparts.category_id',
                array('COUNT(DISTINCT cliparts.clipart_id)'))
            ->where('clipart_category.category_id = main_table.category_id')
            ->orWhere('clipart_category.path LIKE ?', $concatExpr);
        $countExpr = $this->getConnection()
            ->quoteInto(new Zend_Db_Expr('('. $countSelect .') > ?'), 0);
        $this->getSelect()->where($countExpr);

        return $this;
    }
}