<?php 
/**
 * GoMage.com extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the GoMage ProductDesigner module to newer versions in the future.
 * If you wish to customize the GoMage ProductDesigner module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @copyright  Copyright (C) 2013 GoMage.com (http://www.gomage.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Cart configurable item renderer
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Checkout_Cart_Item_Renderer_Configurable
    extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable
{
    /**
     * Return item design option value
     *
     * @return bool|string
     */
    public function getDesignOption()
    {
        $item = $this->getItem();
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
}
