<?php
class GoMage_ProductDesigner_Model_File_Uploader extends Mage_Core_Model_File_Uploader {
    public function getFile() {
        $file = $this->_file;
        return new Varien_Object($file);
    }

    public function save($destinationFolder, $newFileName = null) {
        $this->_file['name'] = $this->getConvertHelper()->format($this->_file['name']);
        return parent::save($destinationFolder, $newFileName);
    }

    protected function getConvertHelper() {
        return Mage::helper('designer/convert');
    }
}