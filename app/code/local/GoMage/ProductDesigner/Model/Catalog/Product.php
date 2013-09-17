<?php
class GoMage_ProductDesigner_Model_Catalog_Product extends Mage_Catalog_Model_Product
{
    /**
     * Retrive media gallery images
     *
     * @return Varien_Data_Collection
     */
    public function getMediaGalleryImages($skipExclude = false)
    {
        if(!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            if ($designGroupId = Mage::app()->getRequest()->getParam('design_id', false)) {
                $designImages = Mage::getModel('gmpd/design')->getDesignsByGroupId($designGroupId);
                $designImages = $this->_prepareDesignImages($designImages);
            }
            foreach ($this->getMediaGallery('images') as $image) {
                if ($image['disabled'] && !$skipExclude) {
                    continue;
                }
                if (!empty($designImages) && isset($designImages[$image['value_id']])) {
                    $designImage = $designImages[$image['value_id']];
                    $mediaConfig = $this->getDesignMediaConfig();
                    $image['origin_file'] = $image['file'];
                    $image['file'] = $designImage['design'];
                    $image['design_id'] = $designGroupId;
                } else {
                    $mediaConfig = $this->getMediaConfig();
                }
                $image['url'] = $mediaConfig->getMediaUrl($image['file']);
                $image['path'] = $mediaConfig->getMediaPath($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $images->addItem(new Varien_Object($image));
            }
            $this->setData('media_gallery_images', $images);
        }

        return $this->getData('media_gallery_images');
    }

    protected function _prepareDesignImages($designImages)
    {
        $designImagesArray = array();
        foreach($designImages as $designImage) {
            $designImagesArray[$designImage->getImageId()] = $designImage;
        }
        return $designImagesArray;
    }

    public function getImage()
    {
        $imageFile = $this->getData('image');
        if($imageFile) {
            $images = $this->getMediaGalleryImages(true);
            foreach($images as $image) {
                if($image->getOriginFile() == $imageFile) {
                    $this->setData('image', $image->getFile());
                }
            }
        }
        return $this->getData('image');
    }

    public function getDesignMediaConfig()
    {
        return Mage::getSingleton('gmpd/design_config');
    }

    /**
     * Retrieve Product URL
     *
     * @param string $designGroupId Design Id
     * @param bool   $useSid        Use SID
     * @return string
     */
    public function getDesignedProductUrl($designGroupId, $useSid = null)
    {
        if ($useSid === null) {
            $useSid = Mage::app()->getUseSessionInUrl();
        }

        $params = array();
        if (!$useSid) {
            $params['_nosid'] = true;
        }
        if ($designGroupId) {
            $params['_query'] = array('design_id' => $designGroupId);
        }
        return $this->getUrlModel()->getUrl($this, $params);
    }
}