<?php
class GoMage_ProductDesigner_Model_Catalog_Product extends Mage_Catalog_Model_Product {
    public function getMediaGalleryImages($fromDesignerPage=false)
    {
        $images = parent::getMediaGalleryImages();
        if($images && !$fromDesignerPage) {
            $imageIds = $images->getAllIds();
            $designImages = Mage::getSingleton('gmpd/design')->getCollectionByImageIds($imageIds);

            if($designImages->count() > 0) {
                $designImagesArray = $this->prepareDesignImagesArray($designImages);

                foreach($images as $image) {
                    $newImage = $image;
                    if(isset($designImagesArray[$image->getId()])) {
                        $designImage = $designImagesArray[$image->getId()];
                        $newImage = new Varien_Object($image->getData());
                        $newImage->addData(array(
                            'file' => $designImage->getDesign(),
                            'url' => $designImage->getImageUrl(),
                            'path' => $designImage->getImagePath()
                        ));
                    }
                    $images->removeItemByKey($image->getId());
                    $images->addItem($newImage);
                }
                $this->setData('media_gallery_images', $images);
            }
        }
        return $images;
    }

    protected function prepareDesignImagesArray($designImages) {
        $designImagesArray = array();
        foreach($designImages as $designImage) {
            $designImagesArray[$designImage->getImageId()] = $designImage;
        }
        return $designImagesArray;
    }

    public function getImage() {
        $image = $this->getData('image');
        if($image) {
            $galleryImages = $this->getMediaGalleryImages();
            foreach($galleryImages as $galleryImage) {
                if(preg_match('#'.preg_quote($image).'$#', $galleryImage->getFile(), $matches) != 0) {
                    $image = $galleryImage->getFile();
                }
                $this->setData('image', $image);
            }
        }
        return $image;
    }
}