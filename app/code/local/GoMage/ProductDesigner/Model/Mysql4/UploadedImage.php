<?php

class GoMage_ProductDesigner_Model_Mysql4_UploadedImage extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('gmpd/uploadedImage', 'image_id');
    }
}

