<?php

class GoMage_ProductDesigner_Model_Mysql4_Clipart extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('gmpd/clipart', 'clipart_id');
    }
}

