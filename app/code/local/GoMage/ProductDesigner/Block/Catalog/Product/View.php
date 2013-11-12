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
 * Catalog product view block
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * Return true if product has options or has product design
     *
     * @return bool
     */
    public function hasOptions()
    {
        $hasOption = $this->getProduct()->getTypeInstance(true)->hasOptions($this->getProduct());

        if ($hasOption || $this->hasDesign()) {
            return true;
        }

        return false;
    }

    public function hasDesign()
    {
        $design = Mage::helper('gomage_designer')->getProductDesign($this->getProduct());
        return in_array($this->getProduct()->getTypeId(), Mage::helper('gomage_designer')->getAllowedProductTypes())
            && $design && $design->getId();
    }

    public function getDesignUrl()
    {
        $product = $this->getProduct();
        $params = array('_query' => array('id' => $product->getId()));
        return $this->getUrl('designer', $params);
    }

    public function addToCartDisabled()
    {
        if ($this->hasDesign() || !$this->getProduct()->getEnableProductDesigner()) {
            return false;
        }

        return $this->getProduct()->getEnableProductDesigner() && Mage::getStoreConfig('gomage_designer/general/add_to_cart_button');
    }
}
