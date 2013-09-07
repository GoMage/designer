<?php
class GoMage_ProductDesigner_Model_Mysql4_Font_Collection extends GoMage_ProductDesigner_Model_Mysql4_Collection_Abstract {
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix              = 'designer_font_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject              = 'designer_font_collection';

    protected function _construct()
    {
        $this->_init('gmpd/font');
        $this
            ->addFilterToMap('font_id', 'main_table.font_id');
    }
}