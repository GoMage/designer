<?php
class GoMage_ProductDesigner_Model_Design extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('gmpd/design');
    }

    public function getConfig()
    {
        return Mage::getSingleton('gmpd/design_config');
    }

    public function saveDesign($images, $product, $prices)
    {
        $customerId = (int) Mage::getSingleton('customer/session')->getCustomerId();

        $this->setData(array(
            'customer_id' => $customerId ? $customerId : null,
            'session_id' => $customerId ? null : Mage::helper('designer')->getDesignerSessionId(),
            'product_id' => $product->getId(),
            'created_date' => Mage::getModel('core/date')->gmtDate(),
            'price' => isset($prices['sub_total']) ? $prices['sub_total'] : 0
        ))->save();

        $imagesPrice = isset($prices['images']) ? $prices['images'] : array();
        foreach ($images as $imageId => $_image) {
            $image = Mage::getModel('gmpd/design_image');
            $imagePrice = isset($imagesPrice[$imageId]) ? $imagesPrice[$imageId] : 0;
            $image->setPrice($imagePrice);
            $image->saveImage($_image, $imageId, $product, $this->getId());
        }

        return $this;
    }
}