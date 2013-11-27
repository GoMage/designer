<?php

class GoMage_ProductDesigner_Model_Mysql4_UploadedImage extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('gomage_designer/uploadedImage', 'image_id');
    }

    public function removeImagesByIds($ids = array())
    {
        $ids = implode(', ', $ids);
        $this->_getWriteAdapter()->delete($this->getMainTable(), "image_id IN ({$ids})");
        return $this;
    }
}

