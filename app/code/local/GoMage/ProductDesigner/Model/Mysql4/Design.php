<?php

class GoMage_ProductDesigner_Model_Mysql4_Design extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('gomage_designer/design', 'design_id');
    }
}

