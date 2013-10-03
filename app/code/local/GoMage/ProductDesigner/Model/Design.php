<?php
class GoMage_ProductDesigner_Model_Design extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gmpd/design');
    }

    /**
     * Return design media config
     *
     * @return GoMage_ProductDesigner_Model_Design_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('gmpd/design_config');
    }

    /**
     * Save design
     *
     * @param Mage_Catalog_Model_Product $product Product
     * @param array                      $data    Data
     * @return $this
     */
    public function saveDesign($product, $data)
    {
        $images = isset($data['images']) ? $data['images'] : false;
        $prices = isset($data['prices']) ? $data['prices'] : array();
        $color  = isset($data['color'])  ? $data['color']  : null;

        if (!$images || empty($images)) {
            return $this;
        }
        $images = Mage::helper('core')->jsonDecode($images);
        $prices = Mage::helper('core')->jsonDecode($prices);
        $customerId = (int) Mage::getSingleton('customer/session')->getCustomerId();

        $this->setData(array(
            'customer_id' => $customerId ? $customerId : null,
            'session_id' => $customerId ? null : Mage::helper('designer')->getDesignerSessionId(),
            'product_id' => $product->getId(),
            'created_date' => Mage::getModel('core/date')->gmtDate(),
            'price' => isset($prices['sub_total']) ? $prices['sub_total'] : 0,
            'color' => $color
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