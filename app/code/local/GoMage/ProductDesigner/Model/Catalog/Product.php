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
            if ($designId = Mage::app()->getRequest()->getParam('design_id', false)) {
                $designImages = $this->getDesignProductImages($designId);
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
                    $image['file'] = $designImage['image'];
                    $image['design_id'] = $designId;
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
     * Retrieve Product URL with design
     *
     * @param string $designId Design Id
     * @param bool   $useSid   Use SID
     * @return string
     */
    public function getDesignedProductUrl($designId, $useSid = null)
    {
        if ($useSid === null) {
            $useSid = Mage::app()->getUseSessionInUrl();
        }

        $params = array();
        if (!$useSid) {
            $params['_nosid'] = true;
        }
        if ($designId) {
            $params['_query'] = array('design_id' => $designId);
        }
        return $this->getUrlModel()->getUrl($this, $params);
    }

    /**
     * Return design product images
     *
     * @param int $designId Design Id
     * @return mixed
     */
    public function getDesignProductImages($designId)
    {
        $collection = Mage::getModel('gmpd/design_image')->getCollection()
            ->addFieldToFilter('design_id', $designId)
            ->addFieldToFilter('product_id', $this->getId());

        return $collection;
    }

    /**
     * Return product colors
     *
     * @return bool
     */
    public function getProductColors()
    {
        if ($this->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return Mage::getResourceModel('gmpd/catalog_product_type_configurable')->getProductColors($this->getId());
        }

        return false;
    }
}