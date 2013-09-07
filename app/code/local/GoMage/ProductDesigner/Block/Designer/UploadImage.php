<?php
//@todo Take needed options from admin settings
class GoMage_ProductDesigner_Block_Designer_UploadImage extends Mage_Core_Block_Template
{
    /**
     * Is license agreements enabled
     *
     * @return bool
     */
    public function licenseAgreementsEnabled()
    {
        return Mage::getStoreConfig('gmpd/upload_image/copyright');
    }

    /**
     * Return license agreements text
     *
     * @return string
     */
    public function getLicenceAgreementsText()
    {
        return Mage::getStoreConfig('gmpd/upload_image/copyright_text');
    }

    /**
     * Is image conditions enabled
     *
     * @return true
     */
    public function imageConditionsEnabled()
    {
        return Mage::getStoreConfig('gmpd/upload_image/conditions');
    }

    /**
     * Return image conditions text
     *
     * @return string
     */
    public function getImageConditionsText()
    {
        return Mage::getStoreConfig('gmpd/upload_image/conditions_text');
    }


}