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
 * Catalog Product view option block
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Catalog_Product_View_Options
    extends Mage_Catalog_Block_Product_View_Options
{
    protected $_designOption;

    /**
     * Return product design option
     *
     * @return bool|Mage_Catalog_Model_Product_Option
     */
    public function getDesignOption()
    {
        if (is_null($this->_designOption)) {
            $design = Mage::helper('designer')->getProductDesign($this->getProduct());
            if ($design && $design->getId()) {
                $option = new Mage_Catalog_Model_Product_Option(array(
                    'id' => $design->getId(),
                    'value' => $design->getId(),
                    'price' => $design->getPrice(),
                    'price_type' => 'fixed',
                ));

                if (!$this->hasOptions()) {
                    $option->setData('decorated_is_last', true);
                }
                $option->setProduct($this->getProduct());
                $priceConfig = $this->_getPriceConfiguration($option);
                $option->setPriceConfig(
                    Mage::helper('core')->jsonEncode($priceConfig)
                );

                $this->_designOption = $option;
            } else {
                $this->_designOption = false;
            }
        }

        return $this->_designOption;
    }

    /**
     * Return product design option html
     *
     * @return string
     */
    public function getDesignOptionHtml()
    {
        if ($option = $this->getDesignOption()) {
            $block = $this->getChild('product_option_design');
            $block->setOption($option);
            return $block->toHtml();
        }

        return '';
    }

    /**
     * Product has design option
     *
     * @return bool
     */
    public function hasDesignOption()
    {
        $option = $this->getDesignOption();
        return $option ? true : false;
    }
}
