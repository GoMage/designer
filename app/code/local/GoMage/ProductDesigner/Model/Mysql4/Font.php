<?php

class GoMage_ProductDesigner_Model_Mysql4_Font extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('gmpd/font', 'font_id');
    }

    public function getFontByName($fontName)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from($this->getMainTable())
            ->where("font LIKE '%{$fontName}%'");

        return $this->_getReadAdapter()->fetchRow($select);
    }
}

