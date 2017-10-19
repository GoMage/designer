<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2017 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.5.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * Return item design option value
     *
     * @return bool|int
     */
    public function getDesignOption()
    {
        $item         = $this->getItem();
        $designOption = $item->getOptionByCode('design');
        if ($designOption && $designOption->getValue()) {
            return $designOption->getValue();
        }

        return false;
    }

    /**
     * Return product url
     *
     * @return null|string
     */
    public function getProductUrl()
    {
        if (!is_null($this->_productUrl)) {
            return $this->_productUrl;
        }
        if ($this->getItem()->getRedirectUrl()) {
            return $this->getItem()->getRedirectUrl();
        }

        $product = $this->getProduct();
        $option  = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }
        $params = array();
        if ($designId = $this->getDesignOption()) {
            $params['_query'] = array('design_id' => $designId);
        }

        return $product->getUrlModel()->getUrl($product, $params);
    }

    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        $params = array('id' => $this->getItem()->getId());
        if ($designId = $this->getDesignOption()) {
            $params['design_id'] = $designId;
        }
        return $this->getUrl(
            'checkout/cart/configure',
            $params
        );
    }

    /**
     * Get product thumbnail image
     *
     * @return GoMage_ProductDesigner_Helper_Image
     */
    public function getProductThumbnail()
    {
        if ($designId = $this->getDesignOption()) {
            if ($image = Mage::getModel('gomage_designer/design')->getDesignThumbnailImage($designId)) {
                return $this->helper('gomage_designer/image_design')->init($image);
            }
        }

        return parent::getProductThumbnail();
    }

    /**
     * Get item delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        $helper = $this->getProCartHelper();
        if (!$helper) {
            return parent::getDeleteUrl();
        }
        $rendered = $this->getRenderedBlock();
        $is_cart  = ($helper->getIsCartPage() || $helper->getChangeAttributeCart() ||
            $helper->getChangeQtyCart() || $helper->isCrosssellAdd() || $helper->isWhishlistMove());

        if ($helper->isProCartEnable() &&
            ($rendered &&
                (($rendered->getNameInLayout() == 'cart_sidebar' || $rendered->getName() == 'cart_sidebar') ||
                    $is_cart))
        ) {
            return 'javascript:GomageProcartConfig.deleteItem(\'' . $this->getUrl(
                'checkout/cart/delete',
                array(
                    'id' => $this->getItem()->getId()
                )
            ) . '\', \'' . $rendered->getNameInLayout() . '\')';
        }

        return parent::getDeleteUrl();

    }

    /**
     * @return bool|Mage_Core_Helper_Abstract
     */
    protected function getProCartHelper()
    {
        $helper  = false;
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (isset($modules['GoMage_Procart']) && $modules['GoMage_Procart']->is('active')) {
            try {
                $helper = Mage::helper('gomage_procart');
            } catch (Exception $e) {
                $helper = false;
            }
        }
        return $helper;
    }


}
