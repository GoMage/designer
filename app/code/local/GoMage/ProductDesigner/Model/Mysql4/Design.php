<?php

class GoMage_ProductDesigner_Model_Mysql4_Design extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('gmpd/design', 'design_id');
    }

    public function getProductIdsByCustomer($customerId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('customer_id = ?', $customerId)
            ->group('product_id')
            ->columns('product_id');

        return $this->_getReadAdapter()->fetchAll($select);
    }
}

