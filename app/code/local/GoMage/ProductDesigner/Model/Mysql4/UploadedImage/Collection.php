<?php
class GoMage_ProductDesigner_Model_Mysql4_UploadedImage_Collection extends GoMage_ProductDesigner_Model_Mysql4_Collection_Abstract {
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix              = 'designer_uploadedImage_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject              = 'designer_uploadedImage_collection';

    protected function _construct()
    {
        $this->_init('gmpd/uploadedImage');
        $this
            ->addFilterToMap('image_id', 'main_table.image_id');
    }
}