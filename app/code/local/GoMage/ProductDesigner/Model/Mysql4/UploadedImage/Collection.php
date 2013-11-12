<?php
class GoMage_ProductDesigner_Model_Mysql4_UploadedImage_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
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
        $this->_init('gomage_designer/uploadedImage');
        $this
            ->addFilterToMap('image_id', 'main_table.image_id');
    }
}