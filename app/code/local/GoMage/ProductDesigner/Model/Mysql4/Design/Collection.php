<?php
class GoMage_ProductDesigner_Model_Mysql4_Design_Collection extends GoMage_ProductDesigner_Model_Mysql4_Collection_Abstract {
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix              = 'designer_design_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject              = 'designer_design_collection';

    protected function _construct()
    {
        $this->_init('gmpd/design');
        $this
            ->addFilterToMap('design_id', 'main_table.design_id');
    }
}