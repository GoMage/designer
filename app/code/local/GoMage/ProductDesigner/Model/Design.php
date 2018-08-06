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
class GoMage_ProductDesigner_Model_Design extends Mage_Core_Model_Abstract
{
    const DESIGN_SIZE_WIDTH = 580;

    const DESIGN_SIZE_HEIGHT = 700;

    const CUSTOM_OPTION_ID = 0;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gomage_designer/design');
    }

    /**
     * Return design media config
     *
     * @return GoMage_ProductDesigner_Model_Design_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('gomage_designer/design_config');
    }

    /**
     * Save design
     *
     * @param Mage_Catalog_Model_Product $product Product
     * @param array $data Data
     * @return $this
     */
    public function saveDesign($product, $data)
    {
        $images  = isset($data['images']) ? $data['images'] : false;
        $prices  = isset($data['prices']) ? $data['prices'] : array();
        $color   = isset($data['color']) && $data['color'] != 'none_color' ? $data['color'] : null;
        $comment = isset($data['comment']) ? $data['comment'] : null;

        if (!$images || empty($images)) {
            return $this;
        }
        if ($images) {
            $images = Mage::helper('core')->jsonDecode($images);
        } else {
            $images = array();
        }

        if ($prices) {
            $prices = Mage::helper('core')->jsonDecode($prices);
        } else {
            $prices = array();
        }

        $customerId = (int)Mage::getSingleton('customer/session')->getCustomerId();

        $this->setData(array(
                'customer_id'  => $customerId ? $customerId : null,
                'session_id'   => $customerId ? null : Mage::helper('gomage_designer')->getDesignerSessionId(),
                'product_id'   => $product->getId(),
                'created_date' => Mage::getModel('core/date')->gmtDate(),
                'price'        => isset($prices['sub_total']) ? $prices['sub_total'] : 0,
                'color'        => $color,
                'comment'      => $comment
            )
        )->save();

        $imagesPrice = isset($prices['images']) ? $prices['images'] : array();
        foreach ($images as $imageId => $_image) {
            $image      = Mage::getModel('gomage_designer/design_image');
            $imagePrice = isset($imagesPrice[$imageId]) ? $imagesPrice[$imageId] : 0;
            $image->setPrice($imagePrice);
            $image->saveImage($_image, $imageId, $product, $this->getId());
        }

        return $this;
    }

    public function getImages($designId)
    {
        $images = Mage::getModel('gomage_designer/design_image')->getCollection()
            ->addFieldToFilter('design_id', $designId);

        return $images;
    }

    public function getDesignThumbnailImage($designId)
    {
        $images = Mage::getModel('gomage_designer/design_image')->getCollection()
            ->addFieldToFilter('design_id', $designId)
            ->setPageSize(1);
        $image  = $images->getFirstItem();
        if ($image && $image->getId()) {
            return $image->getImage();
        }

        return false;
    }

    public function getDesignProduct()
    {
        if ($this->getProductId()) {
            return Mage::getModel('catalog/product')->load($this->getProductId());
        }
        return null;
    }

}
