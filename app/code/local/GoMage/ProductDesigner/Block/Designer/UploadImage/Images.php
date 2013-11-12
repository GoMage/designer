<?php
class GoMage_ProductDesigner_Block_Designer_UploadImage_Images extends Mage_Core_Block_Template
{
    /**
     * Return upload images
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_UploadedImage_Collection
     */
    public function getUploadedImages()
    {
        $uploadedImage = Mage::getModel('gomage_designer/uploadedImage');
        return $uploadedImage->getCustomerUploadedImages();
    }

    /**
     * Return Image url
     *
     * @param string $image Image
     * @return string
     */
    public function getImageUrl($image)
    {
        return Mage::getSingleton('gomage_designer/uploadedImage_config')->getMediaUrl(rawurlencode($image));
    }
}