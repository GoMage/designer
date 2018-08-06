<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.6.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Block_Designer_UploadImage_Images extends Mage_Core_Block_Template
{
    /**
     * Return upload images
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_UploadedImage_Collection
     */
    public function getUploadedImages()
    {
        return Mage::getModel('gomage_designer/uploadedImage')->getCustomerUploadedImages();
    }

    /**
     * Return resized Image url
     *
     * @param string $image Image
     * @return string
     */
    public function getImageUrl($image)
    {
        return Mage::helper('gomage_designer/image_uploaded')->init($image)->resize(64, 64)->__toString();
    }

    /**
     * Return original Image url
     *
     * @param string $image Image
     * @return string
     */
    public function getOriginImageUrl($image)
    {
        return Mage::getSingleton('gomage_designer/uploadedImage_config')->getMediaUrl($image);
    }
}
