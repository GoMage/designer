<?php

class GoMage_ProductDesigner_Model_Mysql4_Clipart extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('gomage_designer/clipart', 'clipart_id');
    }
}

