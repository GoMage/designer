<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.1.0
 * @since        Available since Release 1.0.0
 */

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

