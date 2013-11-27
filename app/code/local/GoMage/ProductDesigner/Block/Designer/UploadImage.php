<?php
class GoMage_ProductDesigner_Block_Designer_UploadImage extends Mage_Core_Block_Template
{
    /**
     * Is license agreements enabled
     *
     * @return bool
     */
    public function licenseAgreementsEnabled()
    {
        return Mage::getStoreConfig('gomage_designer/upload_image/copyright');
    }

    /**
     * Return license agreements text
     *
     * @return string
     */
    public function getLicenceAgreementsText()
    {
        return Mage::getStoreConfig('gomage_designer/upload_image/copyright_text');
    }

    /**
     * Is image conditions enabled
     *
     * @return true
     */
    public function imageConditionsEnabled()
    {
        return Mage::getStoreConfig('gomage_designer/upload_image/conditions');
    }

    /**
     * Return image conditions text
     *
     * @return string
     */
    public function getImageConditionsText()
    {
        return Mage::getStoreConfig('gomage_designer/upload_image/conditions_text');
    }

    public function getAllowedImageExtensions()
    {
        return Mage::getStoreConfig('gomage_designer/upload_image/format');
    }

    protected function _getAllowedImageMimeTypes()
    {
        $allowedFormats = Mage::getStoreConfig('gomage_designer/upload_image/format');
        $allowedFormats = explode(',', $allowedFormats);
        foreach ($allowedFormats as &$format) {
            $format = 'image/'.$format;
        }

        return $allowedFormats;
    }

    public function getAllowedImageMimeTypesJson()
    {
        return Mage::helper('core')->jsonEncode($this->_getAllowedImageMimeTypes());
    }

    public function getAllowedImageMimeTypesString()
    {
        return implode(', ', $this->_getAllowedImageMimeTypes());
    }

    public function getMaxUploadFileSize()
    {
        return (int) Mage::getStoreConfig('gomage_designer/upload_image/size') * 1024 * 1024;
    }

    public function hasImages()
    {
        return count(Mage::getModel('gomage_designer/uploadedImage')->getCustomerUploadedImages()) > 0;
    }

    public function getRemoveImgUrl()
    {
        return $this->getUrl('designer/index/removeUploadedImages');
    }
}